<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farmer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('farm_name', 200);
            $table->string('ocop_certificate')->nullable()->comment('Mã/ảnh chứng nhận OCOP nếu có');
            $table->unsignedTinyInteger('ocop_star_rating')->nullable()->comment('Số sao OCOP (3-5), NULL nếu chưa có');
            $table->string('region', 150)->nullable()->comment('Huyện/xã tại Đồng Tháp');
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farmer_profiles');
    }
};
