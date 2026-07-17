<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Tests\TestCase;

class DrawerTest extends TestCase
{
    public function test_co_du_moc_id_cho_javascript(): void
    {
        $html = view('layouts.app', ['page' => 'home'])->render();

        $this->assertStringContainsString('id="drawer"', $html);
        $this->assertStringContainsString('id="drawerOverlay"', $html);
        $this->assertStringContainsString('id="drawerOpen"', $html);
        $this->assertStringContainsString('id="drawerClose"', $html);
    }

    public function test_khach_thay_nut_dang_nhap_o_chan_drawer(): void
    {
        $html = view('layouts.app', ['page' => 'home'])->render();

        $this->assertStringContainsString('data-drawer-guest', $html);
        $this->assertStringNotContainsString('data-drawer-user', $html);
    }

    public function test_nong_dan_thay_dung_ten_va_vai_tro(): void
    {
        $user = new User(['name' => 'Nguyễn Văn Sáu']);
        $user->setRelation('role', new Role(['name' => 'nong_dan']));

        $this->actingAs($user);

        $html = view('layouts.app', ['page' => 'home'])->render();

        $this->assertStringContainsString('data-drawer-user', $html);
        $this->assertStringContainsString('Nguyễn Văn Sáu', $html);
        $this->assertStringContainsString('Nông dân', $html);
    }

    public function test_nguoi_mua_thay_dung_vai_tro(): void
    {
        $user = new User(['name' => 'Trần Thu Hà']);
        $user->setRelation('role', new Role(['name' => 'nguoi_mua']));

        $this->actingAs($user);

        $html = view('layouts.app', ['page' => 'home'])->render();

        $this->assertStringContainsString('Người mua', $html);
    }

    public function test_link_drawer_dung_trang_duoc_danh_dau_active(): void
    {
        $html = view('layouts.app', ['page' => 'ai'])->render();

        $this->assertMatchesRegularExpression(
            '/data-drawer="ai"[^>]*data-drawer-active="1"/',
            $html
        );
    }

    public function test_co_du_tam_link_va_mot_duong_ke_ngan_nhom(): void
    {
        $html = view('layouts.app', ['page' => 'home'])->render();

        // agrisenai-home.html: 8 link, 1 divider đặt sau 'truyxuat'.
        $this->assertSame(8, substr_count($html, 'data-drawer="'));

        foreach (['home', 'market', 'ai', 'khuyennghi', 'truyxuat', 'cart', 'account', 'settings'] as $key)  {
            $this->assertStringContainsString('data-drawer="' . $key . '"', $html);
        }
    }
}
