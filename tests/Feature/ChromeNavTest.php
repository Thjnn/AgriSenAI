<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

/** Khung nav/drawer/bottom-nav theo dinh vi moi (ho so lo, khong con san/gio hang). */
class ChromeNavTest extends TestCase
{
    private function staff(): User
    {
        $user = new User(['name' => 'Nguyễn Văn Tâm', 'org_name' => 'Vựa xoài Mỹ Xương']);
        $user->setRelation('role', new Role(['name' => Role::NHAN_VIEN]));

        return $user;
    }

    public function test_khach_chi_thay_link_cong_khai_va_nut_dang_nhap(): void
    {
        $html = view('layouts.app', ['page' => 'home'])->render();

        $this->assertStringContainsString('data-topnav="home"', $html);
        $this->assertStringContainsString('data-topnav="lookup"', $html);
        $this->assertStringNotContainsString('data-topnav="lots"', $html);
        $this->assertStringContainsString('data-topnav-guest', $html);
        $this->assertStringContainsString('data-drawer-guest', $html);
    }

    public function test_nhan_vien_thay_lo_hang_ten_va_to_chuc(): void
    {
        $this->actingAs($this->staff());

        $html = view('layouts.app', ['page' => 'lots'])->render();

        $this->assertMatchesRegularExpression('/data-topnav="lots"[^>]*data-topnav-active="1"/', $html);
        $this->assertStringContainsString('data-drawer="new"', $html);
        $this->assertStringContainsString('Vựa xoài Mỹ Xương', $html);
        $this->assertStringContainsString('Đăng xuất', $html);
    }

    public function test_khong_con_san_gio_hang_khuyen_nghi_chatbot(): void
    {
        $html = view('layouts.app', ['page' => 'home'])->render();

        foreach (['/thi-truong', '/gio-hang', '/danh-gia', 'Khuyến nghị', 'chatFab'] as $bo) {
            $this->assertStringNotContainsString($bo, $html, "Còn sót: {$bo}");
        }
    }

    public function test_bottom_nav_nut_giua_la_tao_lo(): void
    {
        $this->assertSame(['home', 'lots', 'new', 'lookup', 'stats'], array_column(config('navigation.bottom_nav'), 'key'));

        $html = view('layouts.app', ['page' => 'new'])->render();
        $this->assertMatchesRegularExpression('/data-bnav="new"[^>]*data-bnav-active="1"/', $html);
    }

    public function test_o_tim_kiem_la_form_tra_cuu_ma_lo(): void
    {
        $html = Blade::render('<x-chrome.mob-header />');

        $this->assertStringContainsString('action="'.route('public.lookup').'"', $html);
        $this->assertStringContainsString('name="ma"', $html);
    }

    public function test_trang_bare_khong_co_nav(): void
    {
        $html = view('layouts.app', ['bare' => true])->render();

        $this->assertStringNotContainsString('aria-label="Điều hướng chính"', $html);
        $this->assertStringNotContainsString('aria-label="Điều hướng dưới"', $html);
    }
}
