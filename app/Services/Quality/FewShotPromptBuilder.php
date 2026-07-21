<?php

namespace App\Services\Quality;

use RuntimeException;

/**
 * Dung noi dung prompt few-shot gui cho Gemini.
 *
 * Few-shot = dat san mot loat cap (ANH -> NHAN) TRUOC anh can cham, de model
 * hieu chinh thang diem theo dung chuan cua minh. Cac cap do chinh la 6 anh
 * trong storage/app/reference/<loai>/ va 6 nhan trong labels.json.
 *
 * File nay chay o MOI request — labels.json khong phai file train, no la
 * NOI DUNG PROMPT.
 */
class FewShotPromptBuilder
{
    /**
     * Chi dan he thong: dinh nghia thang hang, tieu chi, va cac LUAT CHONG SAI
     * ma anh mau khong day duoc (vi dataset khong co anh minh hoa).
     */
    public function systemInstruction(): string
    {
        $grades = config('agrisenai.grades');
        $defects = implode(', ', config('agrisenai.defect_types'));
        $severities = implode(', ', config('agrisenai.defect_severities'));

        $thang = '';
        foreach ($grades as $key => $g) {
            $thang .= sprintf(
                "- `%s` (%s): điểm từ %.1f trở lên, tổng khuyết tật bề mặt tối đa %d%%\n",
                $key, $g['nhan'], $g['diem_toi_thieu'], $g['dien_tich_khuyet_tat_toi_da']
            );
        }

        return <<<TXT
        Bạn là chuyên gia phân loại nông sản tươi, đang chấm chất lượng cho sàn nông sản Đồng Tháp.
        Người dùng là nông dân. Trả lời bằng tiếng Việt, dễ hiểu, không dùng từ chuyên môn rắc rối.

        ## Thang hạng (neo theo TCVN 9766:2013)
        {$thang}
        Nguyên tắc phân hạng: xét theo TỔNG DIỆN TÍCH khuyết tật và ĐỘ SÂU của nó,
        KHÔNG xét theo tên bệnh. Một quả có một vết sẹo nhỏ 2% và một quả nứt toác
        nửa vỏ có thể cùng là bệnh loét, nhưng khác hạng hoàn toàn.

        Khuyết tật ĂN VÀO THỊT QUẢ (thối, mốc, nứt sâu, vết lõm xuống dưới mặt vỏ)
        thì KHÔNG còn xét diện tích nữa — quả `khong_dat`.

        // GHI CHU CHO NGUOI BAO TRI — dung them "luat nghiem khac" vao day.
        // Da thu: viet vao prompt cac cau kieu "khi phan van hay chon hang thap hon"
        // va "dung uoc luong dien tich de dat" -> ket qua do tren 25 anh test:
        //   - Van 0/2 tren `khong_dat` (KHONG cai thien duoc gi o cho quan trong nhat)
        //   - Sinh them loi MOI: mot qua xoai xanh SACH bi cham `hang_2` (lech 2 bac)
        //   - Ti le khop 56% -> 60%, nhung n=25 co sai so ~±10pp nen khong co y nghia
        // Ket luan: CHU KHONG THAY DUOC ANH. Van de that la thieu anh qua hong lam
        // neo san. Xem docs/quy-trinh-xay-module-ai.md muc 5 va 12.

        ## Chỉ chấm những gì NHÌN THẤY ĐƯỢC
        Bạn CHỈ được chấm: màu sắc, bề mặt, hình dạng, độ đồng đều, độ chín.
        TUYỆT ĐỐI KHÔNG suy đoán khối lượng (gram) hay độ ngọt (Brix) — hai thứ đó
        nông dân tự nhập, ảnh không cho biết được.

        ## LUẬT QUAN TRỌNG: vỏ xanh KHÔNG phải là lỗi
        Nông dân khai `muc_dich_ban` khi đăng bán. Bạn chấm `ripeness` SO VỚI mục đích đó:
        - `an_xanh`: quả bán để ăn sống (xoài xanh chấm muối ớt, cam/quýt vỏ xanh).
          Vỏ xanh, thịt chắc là ĐÚNG YÊU CẦU → `ripeness` phải CAO. Không được trừ điểm
          vì "chưa chín".
        - `an_chin`: quả bán để ăn chín. Lúc này mới xét độ chín theo nghĩa thông thường.

        Đặc biệt: CAM SÀNH Việt Nam khi chín tới VẪN CÓ VỎ XANH SẦN. Đó là đặc trưng
        của giống, không phải dấu hiệu chưa chín hay nhiễm bệnh.

        ## Khuyết tật
        - `type` chỉ được chọn trong: {$defects}
        - `severity` chỉ được chọn trong: {$severities}
        - `area_percent`: ước lượng % bề mặt quả bị ảnh hưởng (số nguyên)

        ## Nếu ảnh không dùng được
        Nếu ảnh mờ, quá tối, không thấy rõ quả, hoặc trong ảnh không phải nông sản:
        đặt `usable = false`, giải thích ngắn trong `reasoning`, và KHÔNG bịa điểm.

        ## reasoning phải NÊU LÝ DO, không chỉ mô tả
        SAI:  "Quả này có đốm đen"
        ĐÚNG: "Đốm đen rải rác khoảng 12% bề mặt, đã vượt ngưỡng 1/10 của hạng I nhưng
               vẫn dưới 1/4 bề mặt. Các đốm còn nông, chưa ăn vào thịt quả, nên theo
               TCVN 9766:2013 quả đạt hạng II."

        `advice` là lời khuyên bán hàng thiết thực cho nông dân.
        TXT;
    }

    /**
     * Cac cap (anh mau -> nhan vang), dung dinh dang hoi-thoai cua Gemini.
     *
     * @return array<int, array{role: string, parts: array}>
     */
    public function fewShotTurns(string $loai): array
    {
        $dir = storage_path("app/reference/{$loai}");
        $labelsFile = "{$dir}/labels.json";

        // Loai chua co bo anh mau -> cham ZERO-SHOT: khong co cap vi du,
        // Gemini van cham dua tren systemInstruction + anh that. Kem chinh xac
        // hon few-shot nhung khong loi. Bo sung dataset sau de cai thien.
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

            $turns[] = [
                'role' => 'user',
                'parts' => [
                    ['text' => $this->userPrompt($item['label']['muc_dich_ban'] ?? 'an_chin')],
                    $this->imagePart($path),
                ],
            ];

            // Cau tra loi MAU — day chinh la thu day Gemini cach cham.
            $turns[] = [
                'role' => 'model',
                'parts' => [
                    ['text' => json_encode($item['label'], JSON_UNESCAPED_UNICODE)],
                ],
            ];
        }

        // labels.json rong cung coi nhu zero-shot (khong ep loi).
        return $turns;
    }

    /** Luot cuoi: anh that cua nong dan. */
    public function userTurn(string $imagePath, string $mucDichBan): array
    {
        return [
            'role' => 'user',
            'parts' => [
                ['text' => $this->userPrompt($mucDichBan)],
                $this->imagePart($imagePath),
            ],
        ];
    }

    private function userPrompt(string $mucDichBan): string
    {
        $mota = config("agrisenai.muc_dich_ban.{$mucDichBan}", $mucDichBan);

        return "Chấm chất lượng quả trong ảnh.\nMục đích bán nông dân đã khai: `{$mucDichBan}` ({$mota}).";
    }

    private function imagePart(string $path): array
    {
        return [
            'inline_data' => [
                'mime_type' => 'image/jpeg',
                'data' => base64_encode(file_get_contents($path)),
            ],
        ];
    }

    /**
     * Schema ep Gemini tra ve dung cau truc JSON.
     *
     * KHONG co truong khoi luong / Brix — de model khong co cho ma suy doan.
     */
    public function responseSchema(): array
    {
        $scoreProps = [];
        foreach (config('agrisenai.scores') as $s) {
            $scoreProps[$s] = ['type' => 'NUMBER'];
        }

        return [
            'type' => 'OBJECT',
            'properties' => [
                'usable' => ['type' => 'BOOLEAN'],
                'detected_product' => ['type' => 'STRING'],
                'muc_dich_ban' => [
                    'type' => 'STRING',
                    'enum' => array_keys(config('agrisenai.muc_dich_ban')),
                ],
                'grade' => [
                    'type' => 'STRING',
                    'enum' => array_keys(config('agrisenai.grades')),
                ],
                'overall_score' => ['type' => 'NUMBER'],
                'confidence' => ['type' => 'NUMBER'],
                'scores' => [
                    'type' => 'OBJECT',
                    'properties' => $scoreProps,
                    'required' => config('agrisenai.scores'),
                ],
                'defects' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'type' => ['type' => 'STRING', 'enum' => config('agrisenai.defect_types')],
                            'severity' => ['type' => 'STRING', 'enum' => config('agrisenai.defect_severities')],
                            'area_percent' => ['type' => 'NUMBER'],
                            'note' => ['type' => 'STRING'],
                        ],
                        'required' => ['type', 'severity', 'area_percent', 'note'],
                    ],
                ],
                'reasoning' => ['type' => 'STRING'],
                'advice' => ['type' => 'STRING'],
            ],
            'required' => [
                'usable', 'detected_product', 'grade', 'overall_score',
                'confidence', 'scores', 'defects', 'reasoning', 'advice',
            ],
        ];
    }
}
