<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TracePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_qr_endpoint_tra_svg(): void
    {
        $this->seed();
        $p = Product::certified()->first();

        $res = $this->get('/trace/'.$p->qr_code.'/qr');

        $res->assertOk();
        $this->assertStringContainsString('image/svg+xml', $res->headers->get('Content-Type'));
        $this->assertStringContainsString('<svg', $res->getContent());
    }

    public function test_qr_code_khong_ton_tai_404(): void
    {
        $this->seed();
        $this->get('/trace/KHONG-CO-MA-NAY')->assertNotFound();
    }

    public function test_product_chua_co_assessment_404(): void
    {
        $this->seed();
        $p = Product::certified()->first();
        $p->assessments()->delete();

        $this->get('/trace/'.$p->qr_code)->assertNotFound();
    }
}
