<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_man_hinh_nhan_vien_bat_dang_nhap(): void
    {
        $this->get('/lo')->assertRedirect('/dang-nhap');
        $this->get('/thong-ke')->assertRedirect('/dang-nhap');
    }

    public function test_dang_nhap_bang_so_dien_thoai(): void
    {
        $user = User::factory()->create(['phone' => '0911111111', 'password' => 'bimat123']);

        $this->post('/dang-nhap', ['phone' => '0911111111', 'password' => 'bimat123'])
            ->assertRedirect('/lo');

        $this->assertAuthenticatedAs($user);
    }

    public function test_sai_mat_khau_bao_loi(): void
    {
        User::factory()->create(['phone' => '0911111111', 'password' => 'bimat123']);

        $this->from('/dang-nhap')->post('/dang-nhap', ['phone' => '0911111111', 'password' => 'sai'])
            ->assertRedirect('/dang-nhap')
            ->assertSessionHasErrors('phone');

        $this->assertGuest();
    }

    public function test_tai_khoan_bi_khoa_khong_dang_nhap_duoc(): void
    {
        $user = User::factory()->create(['phone' => '0911111111', 'password' => 'bimat123']);
        $user->forceFill(['is_active' => false])->save();

        $this->post('/dang-nhap', ['phone' => '0911111111', 'password' => 'bimat123']);

        $this->assertGuest();
    }

    public function test_dang_xuat(): void
    {
        $this->actingAs(User::factory()->create())->post('/dang-xuat')->assertRedirect('/');

        $this->assertGuest();
    }
}
