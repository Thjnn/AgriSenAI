<?php
// tests/Feature/MarketplaceShowTest.php
namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_trang_chi_tiet_hien_diem_ai_va_nha_vuon(): void
    {
        $this->seed();
        $p = Product::certified()->with('latestAssessment')->first();

        $res = $this->get('/thi-truong/'.$p->id);

        $res->assertOk();
        $res->assertViewIs('marketplace.show');
        $res->assertSee($p->name);
        $res->assertSee((string) (int) $p->latestAssessment->quality_score);
        $res->assertSee($p->farmer->farmerProfile->farm_name);
    }

    public function test_product_khong_active_tra_404(): void
    {
        $this->seed();
        $p = Product::certified()->first();
        $p->update(['status' => 'hidden']);

        $this->get('/thi-truong/'.$p->id)->assertNotFound();
    }
}
