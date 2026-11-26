<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Luot xem trang ho so cong khai (chi khach chua dang nhap). from_qr = mo tu
 * ma QR (link co ?qr=1), con lai = mo link / tra cuu ma lo.
 *
 * ponytail: khong loc bot/F5 nen la so luot, khong phai so nguoi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lot_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lot_id')->constrained()->cascadeOnDelete();
            $table->boolean('from_qr');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lot_scans');
    }
};
