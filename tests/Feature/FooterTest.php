<?php

namespace Tests\Feature;

use Tests\TestCase;

class FooterTest extends TestCase
{
    public function test_an_tren_mobile_bang_class_hidden_lg_block(): void
    {
        $html = view('layouts.app', ['page' => 'home'])->render();

        $this->assertMatchesRegularExpression(
            '/<footer[^>]*class="[^"]*hidden[^"]*lg:block/',
            $html
        );
    }

    public function test_co_du_ba_cot_tu_config(): void
    {
        $html = view('layouts.app', ['page' => 'home'])->render();

        $this->assertStringContainsString('Khám phá', $html);
        $this->assertStringContainsString('Hỗ trợ', $html);
        $this->assertStringContainsString('Liên hệ', $html);
    }

    public function test_co_thong_tin_lien_he(): void
    {
        $html = view('layouts.app', ['page' => 'home'])->render();

        $this->assertStringContainsString('hotro@agrisenai.vn', $html);
        $this->assertStringContainsString('1900 1234', $html);
        $this->assertStringContainsString('TP. Cao Lãnh, Tỉnh Đồng Tháp', $html);
    }

    public function test_co_dong_ban_quyen(): void
    {
        $html = view('layouts.app', ['page' => 'home'])->render();

        $this->assertStringContainsString('© 2026 AgriSenAi', $html);
    }
}
