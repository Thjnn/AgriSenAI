<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** KPI khi DB trong: khong chia cho 0, khong hien % (gop y thay Nguyen 43). */
class StatsKpiEmptyTest extends TestCase
{
    use RefreshDatabase;

    public function test_kpi_khong_chia_cho_0_khi_chua_co_du_lieu(): void
    {
        $this->actingAs(User::factory()->admin()->create())->get('/thong-ke')
            ->assertOk()
            ->assertSee('Tỷ lệ ảnh phải chụp lại')
            ->assertSee('Chưa có dữ liệu')
            ->assertDontSee('NAN');
    }
}
