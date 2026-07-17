<?php

namespace App\Services\Quality;

use GdImage;
use RuntimeException;

/**
 * Chuan hoa anh nong san truoc khi dua vao Gemini.
 *
 * Dung CHUNG cho ca hai duong:
 *   - anh reference (chay mot lan qua `php artisan reference:build`)
 *   - anh nong dan upload (chay moi request)
 *
 * Hai duong nay BAT BUOC phai qua cung mot ham. Neu chi chuan hoa mot ben,
 * anh mau va anh that se lech phan phoi va AI cham sai.
 *
 * Cac buoc:
 *   1. Xoay theo EXIF  <- anh iPhone co Orientation=6, khong xoay thi qua nam ngang
 *   2. Cat vuong (theo khung cho truoc, hoac cat giua)
 *   3. Resize ve 768x768
 *   4. Luu JPEG quality 85 (phang alpha len nen xam)
 */
class ImageNormalizer
{
    public function __construct(
        private readonly int $size = 0,
        private readonly int $quality = 0,
    ) {
    }

    /**
     * @param  string      $src   Anh nguon (.jpg/.png/.webp)
     * @param  string      $dst   Duong dan JPEG dau ra
     * @param  int[]|null  $crop  [left, top, side] theo PIXEL cua anh DA XOAY EXIF.
     *                            Null = tu dong cat vuong o giua.
     */
    public function normalize(string $src, string $dst, ?array $crop = null): void
    {
        $size = $this->size ?: (int) config('agrisenai.image.size', 768);
        $quality = $this->quality ?: (int) config('agrisenai.image.quality', 85);

        $img = $this->load($src);
        $img = $this->applyExifOrientation($img, $src);

        [$l, $t, $side] = $crop ?? $this->centerSquare($img);
        [$l, $t, $side] = $this->clampToImage($img, $l, $t, $side);

        $out = imagecreatetruecolor($size, $size);
        [$r, $g, $b] = config('agrisenai.image.bg', [128, 128, 128]);
        imagefill($out, 0, 0, imagecolorallocate($out, $r, $g, $b));

        imagecopyresampled($out, $img, 0, 0, $l, $t, $size, $size, $side, $side);

        if (! is_dir(dirname($dst))) {
            mkdir(dirname($dst), 0777, true);
        }
        if (! imagejpeg($out, $dst, $quality)) {
            throw new RuntimeException("Khong ghi duoc anh: {$dst}");
        }

        imagedestroy($img);
        imagedestroy($out);
    }

    private function load(string $path): GdImage
    {
        if (! is_file($path)) {
            throw new RuntimeException("Khong thay anh: {$path}");
        }

        $info = getimagesize($path);
        if ($info === false) {
            throw new RuntimeException("Khong doc duoc anh: {$path}");
        }

        $img = match ($info[2]) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($path),
            IMAGETYPE_PNG => imagecreatefrompng($path),
            IMAGETYPE_WEBP => imagecreatefromwebp($path),
            default => throw new RuntimeException(
                "Dinh dang khong ho tro ({$info['mime']}): {$path}"
            ),
        };

        if ($img === false) {
            throw new RuntimeException("GD khong mo duoc anh: {$path}");
        }

        return $img;
    }

    /**
     * Anh chup bang dien thoai luu pixel THO va mot the EXIF Orientation bao
     * huong hien thi. GD bo qua the do -> anh vao Gemini bi xoay 90 do.
     *
     * 20/69 anh xoai trong dataset dinh loi nay (Orientation=6).
     */
    private function applyExifOrientation(GdImage $img, string $path): GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $img;
        }

        $exif = @exif_read_data($path);
        $orientation = $exif['Orientation'] ?? 1;

        $rotated = match ($orientation) {
            3 => imagerotate($img, 180, 0),
            6 => imagerotate($img, -90, 0),
            8 => imagerotate($img, 90, 0),
            default => null,
        };

        if ($rotated === null || $rotated === false) {
            return $img;
        }

        imagedestroy($img);

        return $rotated;
    }

    /**
     * Cat vuong o GIUA anh — dung khi khong biet qua nam o dau (anh nong dan upload).
     *
     * HAN CHE DA BIET: qua nam lech ra mep se bi xen mat mot phan. Da kiem chung
     * bang mot anh that: qua bi cat mat dinh, tuy AI van cham dung.
     *
     * Chua sua bang cach do tim qua tu dong, vi da THU va THAT BAI: hue cua vo xoai
     * vang trung voi hue cua mat ban go, phan vung theo mau khong tach duoc.
     * Giai phap dang dung la RE HON: huong dan nguoi dung dat qua vao giua khung
     * (xem resources/views/quality/index.blade.php). Buoc tiep theo: ve khung
     * huong dan len man hinh chup.
     *
     * @return int[] [left, top, side]
     */
    private function centerSquare(GdImage $img): array
    {
        $w = imagesx($img);
        $h = imagesy($img);
        $side = min($w, $h);

        return [intdiv($w - $side, 2), intdiv($h - $side, 2), $side];
    }

    /** @return int[] [left, top, side] — khung luon nam tron trong anh */
    private function clampToImage(GdImage $img, int $l, int $t, int $side): array
    {
        $w = imagesx($img);
        $h = imagesy($img);

        $side = max(1, min($side, $w, $h));
        $l = max(0, min($l, $w - $side));
        $t = max(0, min($t, $h - $side));

        return [$l, $t, $side];
    }
}
