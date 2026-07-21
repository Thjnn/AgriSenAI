<?php
// tests/Feature/CartTest.php
namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_them_va_xem_gio(): void
    {
        $this->seed();
        $p = Product::certified()->first();

        $this->post('/gio-hang/them/'.$p->id, ['quantity' => 2])
            ->assertRedirect();

        $res = $this->get('/gio-hang');
        $res->assertOk();
        $res->assertSee($p->name);
        $res->assertSessionHas('cart', [$p->id => 2.0]);
    }

    public function test_cap_nhat_va_xoa(): void
    {
        $this->seed();
        $p = Product::certified()->first();
        $this->post('/gio-hang/them/'.$p->id, ['quantity' => 1]);

        $this->patch('/gio-hang/'.$p->id, ['quantity' => 5])->assertRedirect();
        $this->assertSame(5.0, session('cart')[$p->id]);

        $this->delete('/gio-hang/'.$p->id)->assertRedirect();
        $this->assertArrayNotHasKey($p->id, session('cart', []));
    }

    public function test_khong_them_product_khong_active(): void
    {
        $this->seed();
        $p = Product::certified()->first();
        $p->update(['status' => 'hidden']);

        $this->post('/gio-hang/them/'.$p->id, ['quantity' => 1])->assertNotFound();
    }
}
