<?php

namespace App\Services\Quality;

/**
 * Kiem tra anh co dung duoc khong: mo / toi / chay sang / qua nho.
 *
 * Port tu assess_quality() trong MVP mau cua co Hang (Laplacian variance +
 * do sang trung binh + canh ngan). Client cung chay mot ban JS tuong tu de
 * chan som, nhung server KHONG tin client nen kiem lai o day.
 *
 * Chay tren anh DA chuan hoa (768x768) — thu nho tiep ve `sample_side` cho nhanh.
 */
class PhotoQualityChecker
{
    /**
     * @param  int  $origW  Kich thuoc anh GOC (truoc chuan hoa) — de bat anh qua nho.
     * @return array{ok: bool, issues: string[], blur: float, brightness: float, width: int, height: int}
     */
    public function check(string $normalizedPath, int $origW, int $origH): array
    {
        $cfg = config('agrisenai.photo_check');
        $side = $cfg['sample_side'];

        $src = imagecreatefromjpeg($normalizedPath);
        $img = imagecreatetruecolor($side, $side);
        imagecopyresampled($img, $src, 0, 0, 0, 0, $side, $side, imagesx($src), imagesy($src));
        imagedestroy($src);

        // Anh xam (luma) de tinh do sang + Laplacian.
        $g = [];
        $sum = 0;
        for ($y = 0; $y < $side; $y++) {
            for ($x = 0; $x < $side; $x++) {
                $rgb = imagecolorat($img, $x, $y);
                $v = 0.299 * (($rgb >> 16) & 0xFF) + 0.587 * (($rgb >> 8) & 0xFF) + 0.114 * ($rgb & 0xFF);
                $g[$y][$x] = $v;
                $sum += $v;
            }
        }
        imagedestroy($img);

        $brightness = $sum / ($side * $side);

        // Phuong sai cua Laplacian 4-lan-can: anh net co nhieu canh -> phuong sai lon.
        $n = 0;
        $s = 0;
        $s2 = 0;
        for ($y = 1; $y < $side - 1; $y++) {
            for ($x = 1; $x < $side - 1; $x++) {
                $lap = $g[$y - 1][$x] + $g[$y + 1][$x] + $g[$y][$x - 1] + $g[$y][$x + 1] - 4 * $g[$y][$x];
                $s += $lap;
                $s2 += $lap * $lap;
                $n++;
            }
        }
        $blur = $s2 / $n - ($s / $n) ** 2;

        $issues = [];
        if (min($origW, $origH) < $cfg['min_side']) {
            $issues[] = "Ảnh quá nhỏ (cạnh ngắn dưới {$cfg['min_side']} px) — chụp gần hơn hoặc dùng camera sau.";
        }
        if ($blur < $cfg['blur_min']) {
            $issues[] = 'Ảnh bị mờ — giữ máy chắc tay và chạm vào quả để lấy nét.';
        }
        if ($brightness < $cfg['dark_max']) {
            $issues[] = 'Ảnh quá tối — ra chỗ sáng hơn.';
        } elseif ($brightness > $cfg['bright_min']) {
            $issues[] = 'Ảnh bị cháy sáng — tránh nắng gắt chiếu thẳng.';
        }

        return [
            'ok' => $issues === [],
            'issues' => $issues,
            'blur' => round($blur, 1),
            'brightness' => round($brightness, 1),
            'width' => $origW,
            'height' => $origH,
        ];
    }
}
