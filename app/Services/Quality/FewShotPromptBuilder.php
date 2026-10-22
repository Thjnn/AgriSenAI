<?php

namespace App\Services\Quality;

use RuntimeException;

/**
 * Dung noi dung prompt few-shot gui cho Gemini.
 *
 * Few-shot = dat san mot loat cap (ANH -> NHAN) TRUOC anh can xem, de model
 * mo ta theo dung chuan cua minh. Cac cap do chinh la 6 anh trong
 * storage/app/reference/<loai>/ va 6 nhan trong labels.json.
 *
 * AI chi GOI Y quan sat ngoai quan theo 5 tieu chi. Khong cham diem, khong
 * xep hang — nguoi phu trach xac nhan tung tieu chi (gop y co Hang 05/10).
 */
class FewShotPromptBuilder
{
    public function systemInstruction(): string
    {
        $tieuChi = '';
        foreach (config('agrisenai.criteria') as $key => $c) {
            $tieuChi .= sprintf("- `%s` (%s): `value` chọn một trong %s\n",
                $key, $c['label'], implode(', ', array_keys($c['options'])));
        }
        $defects = implode(', ', array_keys(config('agrisenai.defect_types')));
        $severities = implode(', ', config('agrisenai.defect_severities'));

        return <<<TXT
        Bạn là trợ lý kiểm định ngoại quan xoài tại điểm thu gom ở Đồng Tháp.
        Nhiệm vụ: GỢI Ý quan sát ngoại quan theo từng tiêu chí. Người phụ trách sẽ
        đồng ý, sửa hoặc bác bỏ từng gợi ý — bạn KHÔNG ra quyết định, KHÔNG chấm điểm,
        KHÔNG xếp hạng. Trả lời bằng tiếng Việt, ngắn gọn, dễ hiểu.

        ## 5 tiêu chí
        {$tieuChi}
        Mỗi tiêu chí có `note`: một câu nêu ĐIỀU NHÌN THẤY và LÝ DO chọn `value`.
        SAI:  "Quả có đốm đen"
        ĐÚNG: "Vài đốm đen nhỏ rải rác khoảng 2% bề mặt, còn nông, chưa ăn vào thịt quả"

        ## Chỉ nói những gì NHÌN THẤY ĐƯỢC
        TUYỆT ĐỐI KHÔNG suy đoán khối lượng (gram), độ ngọt (Brix) hay giống xoài —
        người dùng tự nhập, ảnh không cho biết được.

        ## Vỏ xanh KHÔNG phải là lỗi
        Người dùng khai `muc_dich_ban`. Xét `ripeness` SO VỚI mục đích đó:
        - `an_xanh`: bán để ăn sống. Vỏ xanh, thịt chắc là ĐÚNG YÊU CẦU → `matches_purpose = true`.
        - `an_chin`: bán để ăn chín.
        Một số giống (Keo, Tứ Quý, Đài Loan...) chín tới VẪN CÒN VỎ XANH — đừng dùng
        màu vỏ một mình để kết luận quả chưa chín.

        ## Khuyết tật (trong `surface.defects`)
        - `type` chỉ chọn trong: {$defects}
        - `severity` chỉ chọn trong: {$severities}
        - `area_percent`: ước lượng % bề mặt bị ảnh hưởng (số nguyên)
        - `into_flesh`: true nếu khuyết tật ăn vào thịt quả (thối, mốc, nứt sâu, vết lõm)
        Không có khuyết tật → `defects` rỗng và `surface.value = sach`.

        ## Nhiều ảnh
        Các ảnh là CÙNG MỘT LÔ. `uniformity` xét độ đồng đều giữa các quả nhìn thấy;
        chỉ có một quả thì `dong_deu` và ghi rõ "chỉ thấy một quả".

        ## Ảnh không dùng được
        Ảnh mờ, quá tối, không thấy rõ quả, hoặc không phải xoài: đặt `usable = false`,
        nêu lý do trong `unusable_reason`, KHÔNG bịa quan sát.

        `confidence` (0–1): bạn chắc chắn đến đâu về toàn bộ gợi ý.
        TXT;
    }

    /**
     * Cac cap (anh mau -> nhan mau), dung dinh dang hoi-thoai cua Gemini.
     *
     * @return array<int, array{role: string, parts: array}>
     */
    public function fewShotTurns(string $loai): array
    {
        $dir = storage_path("app/reference/{$loai}");
        $labelsFile = "{$dir}/labels.json";

        // Chua co bo anh mau -> zero-shot (kem chinh xac hon nhung khong loi).
        if (! is_file($labelsFile)) {
            return [];
        }

        $data = json_decode(file_get_contents($labelsFile), true, 512, JSON_THROW_ON_ERROR);
        $turns = [];

        foreach ($data['labels'] ?? [] as $item) {
            $path = "{$dir}/{$item['file']}";
            if (! is_file($path)) {
                throw new RuntimeException("Thieu anh mau: {$path}");
            }

            $turns[] = $this->userTurn([$path], $item['muc_dich_ban'] ?? 'an_chin');
            // Cau tra loi MAU — day chinh la thu day Gemini cach mo ta.
            $turns[] = [
                'role' => 'model',
                'parts' => [['text' => json_encode($item['label'], JSON_UNESCAPED_UNICODE)]],
            ];
        }

        return $turns;
    }

    /** @param  string[]  $imagePaths  Anh DA chuan hoa cua cung mot lo */
    public function userTurn(array $imagePaths, string $mucDichBan): array
    {
        $mota = config("agrisenai.muc_dich_ban.{$mucDichBan}", $mucDichBan);
        $parts = [['text' => "Gợi ý ngoại quan lô xoài trong ảnh.\nMục đích bán người dùng đã khai: `{$mucDichBan}` ({$mota})."]];

        foreach ($imagePaths as $path) {
            $parts[] = [
                'inline_data' => [
                    'mime_type' => 'image/jpeg',
                    'data' => base64_encode(file_get_contents($path)),
                ],
            ];
        }

        return ['role' => 'user', 'parts' => $parts];
    }

    /**
     * Schema ep Gemini tra dung cau truc. KHONG co grade / overall_score /
     * scores, cung khong co khoi luong / Brix — de model khong co cho ma bia.
     */
    public function responseSchema(): array
    {
        $criterion = fn (string $key, array $extra = []) => [
            'type' => 'OBJECT',
            'properties' => [
                'value' => ['type' => 'STRING', 'enum' => array_keys(config("agrisenai.criteria.{$key}.options"))],
                'note' => ['type' => 'STRING'],
            ] + $extra,
            'required' => ['value', 'note', ...array_keys($extra)],
        ];

        return [
            'type' => 'OBJECT',
            'properties' => [
                'usable' => ['type' => 'BOOLEAN'],
                'unusable_reason' => ['type' => 'STRING', 'nullable' => true],
                'detected_product' => ['type' => 'STRING'],
                'color' => $criterion('color', [
                    'distribution' => [
                        'type' => 'ARRAY',
                        'items' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'name' => ['type' => 'STRING'],
                                'percent' => ['type' => 'NUMBER'],
                            ],
                            'required' => ['name', 'percent'],
                        ],
                    ],
                ]),
                'ripeness' => $criterion('ripeness', ['matches_purpose' => ['type' => 'BOOLEAN']]),
                'surface' => $criterion('surface', [
                    'defects' => [
                        'type' => 'ARRAY',
                        'items' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'type' => ['type' => 'STRING', 'enum' => array_keys(config('agrisenai.defect_types'))],
                                'severity' => ['type' => 'STRING', 'enum' => config('agrisenai.defect_severities')],
                                'area_percent' => ['type' => 'NUMBER'],
                                'location' => ['type' => 'STRING'],
                                'into_flesh' => ['type' => 'BOOLEAN'],
                            ],
                            'required' => ['type', 'severity', 'area_percent', 'into_flesh'],
                        ],
                    ],
                ]),
                'shape' => $criterion('shape'),
                'uniformity' => $criterion('uniformity'),
                'confidence' => ['type' => 'NUMBER'],
            ],
            'required' => ['usable', 'detected_product', 'confidence'],
        ];
    }
}
