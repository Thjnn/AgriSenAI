<?php
// tests/Feature/CheckoutTest.php
namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_dat_hang_tao_order_va_order_items(): void
    {
        $this->seed();
        $p = Product::certified()->first();

        $res = $this->withSession(['cart' => [$p->id => 3.0]])
            ->post('/thanh-toan', [
                'shipping_address' => '12 Nguyễn Huệ, Cao Lãnh',
                'note' => 'Giao giờ hành chính',
            ]);

        $order = Order::latest('id')->first();
        $this->assertNotNull($order);
        $this->assertSame('pending', $order->status);
        $this->assertEqualsWithDelta((float) $p->price * 3, (float) $order->total_amount, 0.01);
        $this->assertSame(1, $order->items()->count());
        $res->assertRedirect(route('order.show', $order->order_code));
    }

    public function test_gio_rong_khong_dat_duoc(): void
    {
        $this->seed();
        $this->withSession(['cart' => []])
            ->post('/thanh-toan', ['shipping_address' => 'x'])
            ->assertStatus(400);
    }

    public function test_thieu_dia_chi_bao_loi(): void
    {
        $this->seed();
        $p = Product::certified()->first();
        $this->withSession(['cart' => [$p->id => 1.0]])
            ->post('/thanh-toan', [])
            ->assertSessionHasErrors('shipping_address');
    }
}
