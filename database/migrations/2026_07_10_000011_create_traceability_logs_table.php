<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('traceability_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('stage', 100)->comment('thu_hoach / dong_goi / van_chuyen / dang_ban...');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->timestamp('happened_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('traceability_logs');
    }
};
