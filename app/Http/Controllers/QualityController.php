<?php

namespace App\Http\Controllers;

use App\Services\Quality\ImageNormalizer;
use App\Services\Quality\QualityAssessor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class QualityController extends Controller
{
    public function __construct(
        private readonly QualityAssessor $assessor,
        private readonly ImageNormalizer $normalizer,
    ) {
    }

    public function index()
    {
        return view('quality.index', [
            'mucDich' => config('agrisenai.muc_dich_ban'),
            'loaiCoSan' => $this->loaiCoSan(),
        ]);
    }

    public function assess(Request $request)
    {
        $data = $request->validate([
            'anh' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'loai' => ['required', Rule::in($this->loaiCoSan())],
            'muc_dich_ban' => ['required', Rule::in(array_keys(config('agrisenai.muc_dich_ban')))],
        ], [
            'anh.required' => 'Vui lòng chọn ảnh nông sản.',
            'anh.image' => 'File tải lên phải là ảnh.',
            'anh.mimes' => 'Chỉ nhận ảnh JPG, PNG hoặc WEBP.',
            'anh.max' => 'Ảnh không được lớn hơn 8 MB.',
        ]);

        // Chuan hoa TRUOC de: (1) hien lai dung tam anh ma AI da nhin thay,
        // (2) khoi phai chuan hoa lai lan nua ben trong QualityAssessor.
        $chuanHoa = tempnam(sys_get_temp_dir(), 'agrisen_').'.jpg';

        try {
            $this->normalizer->normalize($request->file('anh')->getRealPath(), $chuanHoa);

            $ketQua = $this->assessor->assess(
                imagePath: $chuanHoa,
                loai: $data['loai'],
                mucDichBan: $data['muc_dich_ban'],
                daChuanHoa: true,
            );

            $anhBase64 = 'data:image/jpeg;base64,'.base64_encode(file_get_contents($chuanHoa));
        } catch (Throwable $e) {
            return back()
                ->withInput()
                ->with('loi', 'Không chấm được ảnh: '.$e->getMessage());
        } finally {
            if (is_file($chuanHoa)) {
                unlink($chuanHoa);
            }
        }

        return view('quality.ket-qua', [
            'kq' => $ketQua,
            'anh' => $anhBase64,
            'loai' => $data['loai'],
            'mucDichBan' => $data['muc_dich_ban'],
            'grades' => config('agrisenai.grades'),
        ]);
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
