<?php
// tests/Feature/MarketplaceIndexTest.php
namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_trang_san_hien_san_pham_chung_nhan(): void
    {
        $this->seed();
        $p = Product::certified()->with('latestAssessment')->first();

        $res = $this->get('/thi-truong');

        $res->assertOk();
        $res->assertViewIs('marketplace.index');
        $res->assertSee($p->name);
        $res->assertSee('AI chứng nhận');
    }
}
