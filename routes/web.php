<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LotController;
use App\Http\Controllers\PublicLotController;
use App\Http\Controllers\StatsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/dang-nhap', [AuthController::class, 'show'])->name('login');
    Route::post('/dang-nhap', [AuthController::class, 'login'])->middleware('throttle:10,1');
});
Route::post('/dang-xuat', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Ho so lo — nhan vien diem thu gom
Route::middleware('auth')->group(function () {
    Route::get('/lo', [LotController::class, 'index'])->name('lots.index');
    Route::get('/lo/tao', [LotController::class, 'create'])->name('lots.create');
    Route::post('/lo', [LotController::class, 'store'])->name('lots.store');
    Route::get('/lo/{lot}', [LotController::class, 'show'])->name('lots.show');
    Route::post('/lo/{lot}/anh', [LotController::class, 'storePhoto'])->name('lots.photos.store');
    Route::delete('/lo/{lot}/anh/{photo}', [LotController::class, 'destroyPhoto'])->name('lots.photos.destroy');
    Route::post('/lo/{lot}/phan-tich', [LotController::class, 'analyze'])->name('lots.analyze');
    Route::get('/lo/{lot}/phan-tich', [LotController::class, 'analysisStatus'])->name('lots.analysis');
    Route::post('/lo/{lot}/xac-nhan', [LotController::class, 'confirm'])->name('lots.confirm');
    Route::post('/lo/{lot}/cong-bo', [LotController::class, 'publish'])->name('lots.publish');
    Route::get('/lo/{lot}/tem', [LotController::class, 'label'])->name('lots.label');

    Route::get('/thong-ke', [StatsController::class, 'index'])->name('stats.index');
    Route::get('/thong-ke/dataset', [StatsController::class, 'dataset'])->name('stats.dataset');
});

// Ho so cong khai qua QR — chi doc, khong can dang nhap
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/tra-cuu', [PublicLotController::class, 'lookup'])->name('public.lookup');
    Route::get('/ho-so/{lot}', [PublicLotController::class, 'show'])->name('public.show');
    Route::get('/ho-so/{lot}/qr', [PublicLotController::class, 'qr'])->name('public.qr');
});

Route::view('/offline', 'offline')->name('offline');
