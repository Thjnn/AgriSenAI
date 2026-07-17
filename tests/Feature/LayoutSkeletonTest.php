<?php

namespace Tests\Feature;

use Tests\TestCase;

class LayoutSkeletonTest extends TestCase
{
    public function test_render_duoc_khi_khong_truyen_page(): void
    {
        $html = view('layouts.app')->render();

        $this->assertStringContainsString('<!DOCTYPE html>', $html);
        $this->assertStringContainsString('lang="vi"', $html);
    }

    public function test_co_the_main_boc_noi_dung(): void
    {
        $html = view('layouts.app', ['page' => 'home'])->render();

        $this->assertStringContainsString('<main', $html);
    }

    public function test_title_mac_dinh_co_ten_thuong_hieu(): void
    {
        $html = view('layouts.app')->render();

        $this->assertStringContainsString('AgriSenAi', $html);
    }
}
