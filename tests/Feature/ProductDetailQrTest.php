<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDetailQrTest extends TestCase
{
    use RefreshDatabase;

    public function test_trang_chi_tiet_co_khoi_qr_tro_trace(): void
    {
        $this->seed();
        $p = Product::certified()->first();

        $res = $this->get('/thi-truong/'.$p->id);

        $res->assertOk();
        $res->assertSee(route('trace.qr', $p->qr_code), false);   // anh QR
        $res->assertSee(route('trace.show', $p->qr_code), false); // link nhat ky
    }
}
