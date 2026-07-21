<?php
// tests/Feature/AssessmentIntegrityColumnsTest.php
namespace Tests\Feature;

use App\Models\AiQualityAssessment;
use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentIntegrityColumnsTest extends TestCase
{
    use RefreshDatabase;

    public function test_assessment_luu_duoc_hai_cot_hash(): void
    {
        $role = Role::firstOrCreate(['name' => Role::NONG_DAN]);
        $u = new User();
        $u->name = 'NV';
        $u->email = 'nv@demo.test';
        $u->password = bcrypt('x');
        $u->role_id = $role->id;
        $u->save();
        $cat = Category::create(['name' => 'Xoài', 'slug' => 'xoai']);
        $p = Product::create([
            'farmer_id' => $u->id, 'category_id' => $cat->id, 'name' => 'Xoài',
            'price' => 30000, 'unit' => 'kg', 'quantity_available' => 10,
            'qr_code' => 'QR123', 'status' => 'active',
        ]);

        $a = AiQualityAssessment::create([
            'product_id' => $p->id, 'image_url' => 'products/x.jpg',
            'ripeness_level' => 'chin', 'quality_score' => 90, 'defect_detected' => false,
            'image_sha256' => str_repeat('a', 64), 'integrity_hash' => str_repeat('b', 64),
            'assessed_at' => now(),
        ]);

        $this->assertSame(str_repeat('a', 64), $a->fresh()->image_sha256);
        $this->assertSame(str_repeat('b', 64), $a->fresh()->integrity_hash);
    }
}
