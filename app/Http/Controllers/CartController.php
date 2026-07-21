<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function add(Request $request, Product $product)
    {
        abort_unless($product->status === 'active', 404);

        $qty = max(1, (float) $request->input('quantity', 1));
        $cart = $request->session()->get('cart', []);
        $cart[$product->id] = ($cart[$product->id] ?? 0) + $qty;
        $request->session()->put('cart', $cart);

        return back()->with('ok', 'Đã thêm vào giỏ');
    }

    public function index(Request $request)
    {
        $cart = $request->session()->get('cart', []);
        $products = Product::whereIn('id', array_keys($cart))->with('primaryImage')->get();
        $total = $products->sum(fn ($p) => $p->price * ($cart[$p->id] ?? 0));

        return view('marketplace.cart', compact('products', 'cart', 'total'));
    }

    public function update(Request $request, Product $product)
    {
        $qty = (float) $request->input('quantity', 1);
        $cart = $request->session()->get('cart', []);
        if ($qty <= 0) {
            unset($cart[$product->id]);
        } else {
            $cart[$product->id] = $qty;
        }
        $request->session()->put('cart', $cart);

        return back()->with('ok', 'Đã cập nhật giỏ');
    }

    public function remove(Request $request, Product $product)
    {
        $cart = $request->session()->get('cart', []);
        unset($cart[$product->id]);
        $request->session()->put('cart', $cart);

        return back()->with('ok', 'Đã xoá khỏi giỏ');
    }
}
