<?php

namespace Tests\Feature;

use Tests\TestCase;

class BottomNavTest extends TestCase
{
    private function render(string $page): string
    {
        return view('layouts.app', ['page' => $page])->render();
    }

    public function test_trang_chu_dung_bo_default_co_gio_hang(): void
    {
        $html = $this->render('home');

        $this->assertStringContainsString('data-bnav="cart"', $html);
        $this->assertStringNotContainsString('data-bnav="khuyennghi"', $html);
    }

    public function test_trang_thi_truong_van_dung_bo_default(): void
    {
        $html = $this->render('market');

        $this->assertStringContainsString('data-bnav="cart"', $html);
        $this->assertStringNotContainsString('data-bnav="khuyennghi"', $html);
    }

    public function test_trang_ai_thi_khuyen_nghi_thay_cho_gio_hang(): void
    {
        $html = $this->render('ai');

        $this->assertStringContainsString('data-bnav="khuyennghi"', $html);
        $this->assertStringNotContainsString('data-bnav="cart"', $html);
    }

    public function test_trang_khuyen_nghi_cung_dung_bo_advisory(): void
    {
        $html = $this->render('khuyennghi');

        $this->assertStringContainsString('data-bnav="khuyennghi"', $html);
        $this->assertStringNotContainsString('data-bnav="cart"', $html);
    }

    public function test_page_la_khoa_khong_biet_thi_ve_bo_default(): void
    {
        $html = $this->render('khong-ton-tai');

        $this->assertStringContainsString('data-bnav="cart"', $html);
    }

    public function test_muc_dang_o_duoc_danh_dau_active(): void
    {
        $html = $this->render('market');

        $this->assertMatchesRegularExpression(
            '/data-bnav="market"[^>]*data-bnav-active="1"/',
            $html
        );
    }

    public function test_trang_ai_thi_nut_scan_active(): void
    {
        $html = $this->render('ai');

        $this->assertMatchesRegularExpression(
            '/data-bnav="ai-scan"[^>]*data-bnav-active="1"/',
            $html
        );
    }
}
