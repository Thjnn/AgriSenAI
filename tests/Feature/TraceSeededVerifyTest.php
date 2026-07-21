<?php
// tests/Feature/TraceSeededVerifyTest.php
namespace Tests\Feature;

use App\Models\Product;
use App\Services\TraceabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TraceSeededVerifyTest extends TestCase
{
    use RefreshDatabase;

    public function test_san_pham_seed_co_the_verified(): void
    {
        $this->seed();
        $p = Product::certified()->with('latestAssessment')->first();
        $a = $p->latestAssessment;

        $this->assertNotNull($a->integrity_hash, 'assessment seed phai co integrity_hash');
        $this->assertNotNull($a->image_sha256, 'assessment seed phai co image_sha256');

        $status = app(TraceabilityService::class)->verify($a)['status'];
        $this->assertSame('verified', $status);

        // Sua diem so -> hash khong con khop -> verify() phai bao mismatch.
        $a->quality_score = 1;
        $a->save();
        $mismatchStatus = app(TraceabilityService::class)->verify($a->fresh())['status'];
        $this->assertSame('mismatch', $mismatchStatus);
    }
}
