<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Tests\TestCase;

class TopNavTest extends TestCase
{
    public function test_khach_thay_nut_dang_nhap_dang_ky(): void
    {
        $html = view('layouts.app', ['page' => 'home'])->render();

        $this->assertStringContainsString('data-topnav-guest', $html);
        $this->assertStringNotContainsString('data-topnav-auth', $html);
        $this->assertStringContainsString('Đăng nhập', $html);
        $this->assertStringContainsString('Đăng ký', $html);
    }

    public function test_da_dang_nhap_thi_thay_chuong_gio_avatar(): void
    {
        $user = new User(['name' => 'Trần Thu Hà']);
        $user->setRelation('role', new Role(['name' => 'nguoi_mua']));

        $this->actingAs($user);

        $html = view('layouts.app', ['page' => 'home'])->render();

        $this->assertStringContainsString('data-topnav-auth', $html);
        $this->assertStringNotContainsString('data-topnav-guest', $html);
    }

    public function test_du_nam_link_dieu_huong(): void
    {
        $html = view('layouts.app', ['page' => 'home'])->render();

        $this->assertSame(5, substr_count($html, 'data-topnav="'));
    }

    public function test_link_dung_trang_duoc_danh_dau_active(): void
    {
        $html = view('layouts.app', ['page' => 'khuyennghi'])->render();

        $this->assertMatchesRegularExpression(
            '/data-topnav="khuyennghi"[^>]*data-topnav-active="1"/',
            $html
        );
    }
}
