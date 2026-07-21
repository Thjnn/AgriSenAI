<?php

namespace App\Http\Controllers;

use App\Services\Quality\ImageNormalizer;
use App\Services\Quality\QualityAssessor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class QualityController extends Controller
{
    /** Nhan + emoji hien thi cho tung loai khuyet tat (key -> [nhan, emoji]). */
    private const DEFECT_META = [
        'xuoc_vo' => ['Xước vỏ', '🩹'],
        'nam_nang' => ['Nám nắng', '☀️'],
        'vet_nhua' => ['Vết nhựa', '🟤'],
        'tham' => ['Thâm', '🟣'],
        'sau_benh' => ['Sâu bệnh', '🐛'],
        'nut' => ['Nứt', '⚡'],
        'thoi' => ['Thối', '🦠'],
        'moc' => ['Mốc', '🍄'],
        'meo_mo' => ['Méo mó', '🔶'],
        'greening' => ['Vàng lá gân xanh', '🍋'],
        'canker' => ['Ghẻ loét', '🔴'],
        'blackspot' => ['Đốm đen', '⚫'],
        'khac' => ['Khác', '⚠️'],
    ];

    /** Nhan tieng Viet cho tung tieu chi cham. */
    private const SCORE_LABELS = [
        'color' => 'Màu sắc',
        'surface' => 'Bề mặt vỏ',
        'shape' => 'Hình dạng',
        'uniformity' => 'Độ đồng đều',
        'ripeness' => 'Độ chín',
    ];

    public function __construct(
        private readonly QualityAssessor $assessor,
        private readonly ImageNormalizer $normalizer,
    ) {
    }

    public function index()
    {
        return view('quality.index', [
            'loaiCoSan' => $this->loaiCoSan(),
        ]);
    }

    /**
     * Cham chat luong 1 anh, tra JSON cho trang danh gia (render inline bang JS).
     * Da bo `muc_dich_ban` — de QualityAssessor dung mac dinh 'an_chin'.
     */
    public function assess(Request $request): JsonResponse
    {
        // Validate thu cong + tra JSON: endpoint nay luon duoc goi bang fetch (AJAX),
        // khong dua vao expectsJson() (Accept cu the != */* co the lam Laravel redirect).
        $validator = Validator::make($request->all(), [
            'anh' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'loai' => ['required', Rule::in($this->loaiCoSan())],
        ], [
            'anh.required' => 'Vui lòng chọn ảnh nông sản.',
            'anh.image' => 'File tải lên phải là ảnh.',
            'anh.mimes' => 'Chỉ nhận ảnh JPG, PNG hoặc WEBP.',
            'anh.max' => 'Ảnh không được lớn hơn 8 MB.',
            'loai.required' => 'Vui lòng chọn loại nông sản.',
            'loai.in' => 'Loại nông sản không hợp lệ.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $data = $validator->validated();

        // Chuan hoa TRUOC de khoi phai chuan hoa lai ben trong QualityAssessor.
        $chuanHoa = tempnam(sys_get_temp_dir(), 'agrisen_').'.jpg';

        try {
            $this->normalizer->normalize($request->file('anh')->getRealPath(), $chuanHoa);

            $ketQua = $this->assessor->assess(
                imagePath: $chuanHoa,
                loai: $data['loai'],
                daChuanHoa: true,
            );
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Không chấm được ảnh: '.$e->getMessage(),
            ], 422);
        } finally {
            if (is_file($chuanHoa)) {
                unlink($chuanHoa);
            }
        }

        return response()->json($this->toPayload($ketQua));
    }

    /** Bien ket qua QualityAssessor thanh shape JS trang danh gia can. */
    private function toPayload(array $kq): array
    {
        if (! ($kq['usable'] ?? true)) {
            return [
                'usable' => false,
                'reason' => $kq['reasoning'] ?? 'Ảnh chưa nhìn rõ nông sản.',
            ];
        }

        $grades = config('agrisenai.grades');

        // Diem thanh phan -> nhan tieng Viet.
        $scores = [];
        foreach (($kq['scores'] ?? []) as $key => $val) {
            $scores[self::SCORE_LABELS[$key] ?? ucfirst($key)] = (float) $val;
        }

        // Khuyet tat -> nhan + emoji.
        $defects = [];
        foreach (($kq['defects'] ?? []) as $d) {
            [$nhan, $emoji] = self::DEFECT_META[$d['type']] ?? [str_replace('_', ' ', $d['type']), '⚠️'];
            $defects[] = [
                'type' => $nhan,
                'emoji' => $emoji,
                'severity' => $d['severity'] ?? 'nhe',
                'area_percent' => (int) ($d['area_percent'] ?? 0),
                'note' => $d['note'] ?? '',
            ];
        }

        // Ghi chu ha hang tu rule engine.
        $re = $kq['rule_engine'] ?? [];
        $override = null;
        if ($re['da_ha_hang'] ?? false) {
            $ai = $grades[$re['hang_ai_cham']]['nhan'] ?? $re['hang_ai_cham'] ?? '';
            $cuoi = $grades[$re['hang_cuoi_cung']]['nhan'] ?? $re['hang_cuoi_cung'] ?? '';
            $luat = implode(' ', $re['luat_da_ap_dung'] ?? []);
            $override = trim("AI chấm {$ai}, đối chiếu TCVN phải hạ xuống {$cuoi}. {$luat}");
        }

        return [
            'usable' => true,
            'grade' => $kq['grade'],
            'overall_score' => (float) $kq['overall_score'],
            'confidence' => (float) ($kq['confidence'] ?? 0),
            'detected_product' => $kq['detected_product'] ?? 'Nông sản',
            'scores' => $scores,
            'defects' => $defects,
            'reasoning' => $kq['reasoning'] ?? '',
            'advice' => $kq['advice'] ?? '',
            'override_note' => $override,
        ];
    }

    /** Loai nong san co bo anh few-shot day du (anh + labels.json). */
    private function loaiCoSan(): array
    {
        $loai = [];
        foreach (glob(storage_path('app/reference/*'), GLOB_ONLYDIR) as $dir) {
            if (is_file("{$dir}/labels.json")) {
                $loai[] = basename($dir);
            }
        }

        return $loai;
    }
}
