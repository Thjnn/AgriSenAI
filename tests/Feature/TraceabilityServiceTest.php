<?php

namespace Tests\Feature;

use App\Models\AiQualityAssessment;
use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Services\TraceabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TraceabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeAssessment(array $override = []): AiQualityAssessment
    {
        $role = Role::firstOrCreate(['name' => Role::NONG_DAN]);
        $u = new User();
        $u->name = 'NV'; $u->email = 'nv@demo.test'; $u->password = bcrypt('x'); $u->role_id = $role->id; $u->save();
        $cat = Category::create(['name' => 'Xoài', 'slug' => 'xoai']);
        $p = Product::create([
            'farmer_id' => $u->id, 'category_id' => $cat->id, 'name' => 'Xoài',
            'price' => 30000, 'unit' => 'kg', 'quantity_available' => 10, 'qr_code' => 'QRX', 'status' => 'active',
        ]);

        return AiQualityAssessment::create(array_merge([
            'product_id' => $p->id, 'image_url' => 'products/x.jpg', 'ripeness_level' => 'chin',
            'quality_score' => 90, 'defect_detected' => false, 'assessed_at' => now(),
        ], $override));
    }

    public function test_computeHash_deterministic(): void
    {
        $a = $this->makeAssessment(['image_sha256' => str_repeat('a', 64)]);
        $svc = app(TraceabilityService::class);
        $this->assertSame($svc->computeHash($a->fresh()), $svc->computeHash($a->fresh()));
    }

    public function test_verify_unverified_khi_chua_co_hash(): void
    {
        $a = $this->makeAssessment(); // integrity_hash null
        $this->assertSame('unverified', app(TraceabilityService::class)->verify($a)['status']);
    }

    public function test_verify_verified_va_mismatch(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/x.jpg', 'noi-dung-anh-that');
        $svc = app(TraceabilityService::class);

        $sha = hash('sha256', 'noi-dung-anh-that');
        $a = $this->makeAssessment(['image_sha256' => $sha]);
        $a->integrity_hash = $svc->computeHash($a);
        $a->save();

        $this->assertSame('verified', $svc->verify($a->fresh())['status']);

        // Sua diem -> hash canonical lech -> mismatch
        $a->quality_score = 50;
        $a->save();
        $this->assertSame('mismatch', $svc->verify($a->fresh())['status']);
    }
}
