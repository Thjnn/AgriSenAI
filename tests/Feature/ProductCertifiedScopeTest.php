<?php
// tests/Feature/ProductCertifiedScopeTest.php
namespace Tests\Feature;

use App\Models\AiQualityAssessment;
use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCertifiedScopeTest extends TestCase
{
    use RefreshDatabase;

    /** Tao 1 farmer + category, tra ve id de gan product. */
    private function farmerAndCategory(): array
    {
        $role = Role::firstOrCreate(['name' => Role::NONG_DAN]);
        $u = new User();
        $u->name = 'Nông dân A';
        $u->email = 'a@demo.test';
        $u->password = bcrypt('secret');
        $u->role_id = $role->id;   // role_id khong fillable -> gan truc tiep
        $u->save();

        $cat = Category::create(['name' => 'Xoài', 'slug' => 'xoai']);

        return [$u->id, $cat->id];
    }

    private function makeProduct(int $farmerId, int $catId, array $attrs = []): Product
    {
        return Product::create(array_merge([
            'farmer_id' => $farmerId,
            'category_id' => $catId,
            'name' => 'Xoài Cát',
            'price' => 30000,
            'unit' => 'kg',
            'quantity_available' => 100,
            'qr_code' => 'QR'.uniqid(),
            'status' => 'active',
        ], $attrs));
    }

    public function test_certified_scope_chi_lay_product_active_co_qr_va_co_assessment(): void
    {
        [$farmerId, $catId] = $this->farmerAndCategory();

        // (1) hop le
        $ok = $this->makeProduct($farmerId, $catId);
        AiQualityAssessment::create([
            'product_id' => $ok->id, 'image_url' => 'x.jpg', 'ripeness_level' => 'chin',
            'quality_score' => 90, 'defect_detected' => false, 'assessed_at' => now(),
        ]);

        // (2) draft -> loai
        $this->makeProduct($farmerId, $catId, ['status' => 'draft', 'qr_code' => 'QRd']);
        // (3) khong co qr -> loai
        $this->makeProduct($farmerId, $catId, ['qr_code' => null]);
        // (4) active + qr nhung KHONG co assessment -> loai
        $this->makeProduct($farmerId, $catId, ['qr_code' => 'QRn']);

        $ids = Product::certified()->pluck('id')->all();

        $this->assertSame([$ok->id], $ids);
    }
}
