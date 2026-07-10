<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->constrained('users');
            $table->foreignId('category_id')->constrained('categories');
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->string('unit', 30)->default('kg')->comment('kg, thùng, kg...');
            $table->decimal('quantity_available', 10, 2)->default(0);
            $table->string('qr_code')->nullable()->unique()->comment('Mã QR truy xuất nguồn gốc');
            $table->enum('status', ['draft', 'active', 'sold_out', 'hidden'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
