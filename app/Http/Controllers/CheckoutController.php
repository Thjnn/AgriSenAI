<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function form(Request $request)
    {
        $cart = $request->session()->get('cart', []);
        $products = Product::whereIn('id', array_keys($cart))->get();
        $total = $products->sum(fn ($p) => $p->price * ($cart[$p->id] ?? 0));

        return view('marketplace.checkout', compact('products', 'cart', 'total'));
    }

    public function placeOrder(Request $request)
    {
        $cart = $request->session()->get('cart', []);
        abort_if(empty($cart), 400, 'Giỏ hàng trống');

        $data = $request->validate([
            'shipping_address' => 'required|string|max:255',
            'note' => 'nullable|string',
        ]);

        $products = Product::whereIn('id', array_keys($cart))->get()->keyBy('id');
        // buyer_id NOT NULL: chua login -> fallback ve user nguoi mua demo
        $buyerId = auth()->id() ?? User::where('email', 'muademo@demo.test')->value('id');
        abort_unless($buyerId, 503, 'Chưa có tài khoản người mua — hãy chạy seeder trước.');

        $order = DB::transaction(function () use ($cart, $products, $data, $buyerId) {
            $total = 0;
            foreach ($cart as $pid => $qty) {
                $total += ($products[$pid]->price ?? 0) * $qty;
            }

            $order = Order::create([
                'buyer_id' => $buyerId,
                'order_code' => 'DH'.now()->format('ymd').strtoupper(Str::random(5)),
                'total_amount' => $total,
                'status' => 'pending',
                'shipping_address' => $data['shipping_address'],
                'note' => $data['note'] ?? null,
            ]);

            foreach ($cart as $pid => $qty) {
                if (! isset($products[$pid])) {
                    continue;
                }
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $pid,
                    'quantity' => $qty,
                    'unit_price' => $products[$pid]->price,
                    'subtotal' => $products[$pid]->price * $qty,
                ]);
            }

            return $order;
        });

        $request->session()->forget('cart');

        return redirect()->route('order.show', $order->order_code);
    }

    public function show(Order $order)
    {
        $order->load('items.product');

        return view('marketplace.order-confirm', compact('order'));
    }
}
