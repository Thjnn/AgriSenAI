<?php
// tests/Feature/MarketplaceSeederTest.php
namespace Tests\Feature;

use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_sinh_du_san_pham_chung_nhan_va_1_nguoi_mua(): void
    {
        $this->seed(); // chay DatabaseSeeder

        $this->assertGreaterThanOrEqual(8, Product::certified()->count());
        $this->assertTrue(Role::where('name', Role::NGUOI_MUA)->exists());

        $buyer = User::where('email', 'muademo@demo.test')->first();
        $this->assertNotNull($buyer);

        // moi product chung nhan deu co anh primary + assessment
        $p = Product::certified()->with(['primaryImage', 'latestAssessment'])->first();
        $this->assertNotNull($p->primaryImage);
        $this->assertNotNull($p->latestAssessment);
    }
}
