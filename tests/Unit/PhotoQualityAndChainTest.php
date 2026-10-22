<?php

namespace Tests\Unit;

use App\Services\AuditChain;
use App\Services\Quality\PhotoQualityChecker;
use Tests\TestCase;

class PhotoQualityAndChainTest extends TestCase
{
    private function jpeg(callable $draw): string
    {
        $img = imagecreatetruecolor(768, 768);
        $draw($img);
        $path = tempnam(sys_get_temp_dir(), 'q_').'.jpg';
        imagejpeg($img, $path, 90);

        return $path;
    }

    public function test_anh_that_dat(): void
    {
        $r = app(PhotoQualityChecker::class)->check(storage_path('app/reference/xoai/03_hang_1.jpg'), 1000, 1000);

        $this->assertTrue($r['ok'], json_encode($r));
    }

    public function test_anh_phang_bi_coi_la_mo(): void
    {
        $path = $this->jpeg(fn ($i) => imagefill($i, 0, 0, imagecolorallocate($i, 130, 130, 130)));

        $r = app(PhotoQualityChecker::class)->check($path, 1000, 1000);

        $this->assertFalse($r['ok']);
        $this->assertStringContainsString('mờ', implode(' ', $r['issues']));
    }

    public function test_anh_toi_va_anh_nho(): void
    {
        $path = $this->jpeg(function ($i) {
            for ($x = 0; $x < 768; $x += 4) {
                imageline($i, $x, 0, $x, 767, imagecolorallocate($i, ($x / 4) % 2 ? 40 : 0, 0, 0));
            }
        });

        $r = app(PhotoQualityChecker::class)->check($path, 200, 300);
        $issues = implode(' ', $r['issues']);

        $this->assertStringContainsString('quá tối', $issues);
        $this->assertStringContainsString('quá nhỏ', $issues);
    }

    public function test_canonical_json_khong_phu_thuoc_thu_tu_key(): void
    {
        $this->assertSame(
            AuditChain::canonicalJson(['b' => 1, 'a' => ['y' => 2, 'x' => [3, 1]]]),
            AuditChain::canonicalJson(['a' => ['x' => [3, 1], 'y' => 2], 'b' => 1]),
        );
        $this->assertSame('{"a":"Xoài"}', AuditChain::canonicalJson(['a' => 'Xoài']));
    }
}
