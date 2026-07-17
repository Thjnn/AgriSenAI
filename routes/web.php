<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\QualityController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index']);

// AI danh gia chat luong nong san qua anh
Route::get('/danh-gia', [QualityController::class, 'index'])->name('quality.index');
Route::post('/danh-gia', [QualityController::class, 'assess'])->name('quality.assess');

// Trang thử khung giao diện — đối chiếu với Test-Giao-Dien/agrisenai-home.html
Route::get('/layout-demo', function (Illuminate\Http\Request $request) {
    return view('layout-demo', ['page' => $request->query('page', 'home')]);
});
