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

    public function test_trace_page_public_hien_diem_va_nha_vuon(): void
    {
        $this->seed();
        $p = \App\Models\Product::certified()->with('latestAssessment')->first();

        $res = $this->get('/trace/'.$p->qr_code);

        $res->assertOk();
        $res->assertViewIs('trace.show');
        $res->assertSee($p->name);
        $res->assertSee((string) (int) $p->latestAssessment->quality_score);
    }
}
