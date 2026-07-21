<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\QualityController;
use App\Http\Controllers\TraceController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index']);

// AI danh gia chat luong nong san qua anh
Route::get('/danh-gia', [QualityController::class, 'index'])->name('quality.index');
Route::post('/danh-gia', [QualityController::class, 'assess'])->name('quality.assess');

// San thi truong — danh sach san pham da duoc AI chung nhan
Route::get('/thi-truong', [MarketplaceController::class, 'index'])->name('marketplace.index');
Route::get('/thi-truong/{product}', [MarketplaceController::class, 'show'])->name('products.show');

// Gio hang — luu trong session, khong bang cart
Route::post('/gio-hang/them/{product}', [CartController::class, 'add'])->name('cart.add');
Route::get('/gio-hang', [CartController::class, 'index'])->name('cart.index');
Route::patch('/gio-hang/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/gio-hang/{product}', [CartController::class, 'remove'])->name('cart.remove');

// Checkout mockup — tao order + order_items tu gio session (chua thanh toan/giao van that)
Route::get('/thanh-toan', [CheckoutController::class, 'form'])->name('checkout.form');
Route::post('/thanh-toan', [CheckoutController::class, 'placeOrder'])->name('checkout.place');
Route::get('/don-hang/{order:order_code}', [CheckoutController::class, 'show'])->name('order.show');

// Truy xuat nguon goc qua QR — cong khai, chi doc (khong co POST/PUT/DELETE)
Route::get('/trace/{product:qr_code}', [TraceController::class, 'show'])
    ->name('trace.show')->middleware('throttle:60,1');

Route::get('/trace/{product:qr_code}/qr', [TraceController::class, 'qr'])
    ->name('trace.qr')->middleware('throttle:60,1');

// Trang thử khung giao diện — đối chiếu với Test-Giao-Dien/agrisenai-home.html
Route::get('/layout-demo', function (Illuminate\Http\Request $request) {
    return view('layout-demo', ['page' => $request->query('page', 'home')]);
});
