<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('weather_ref_id')->nullable()
                ->constrained('weather_data_cache')->comment('Liên kết dữ liệu thời tiết dùng để tính toán');
            $table->foreignId('market_price_ref_id')->nullable()
                ->constrained('market_price_reference')->comment('Liên kết giá thị trường dùng để tính toán');
            $table->enum('recommended_action', ['ban_ngay', 'cho_them', 'giam_gia']);
            $table->text('reasoning_text')->nullable()->comment('Giải thích khuyến nghị bằng ngôn ngữ tự nhiên (AI sinh ra)');
            $table->date('valid_until')->nullable()->comment('Khuyến nghị có hiệu lực đến ngày nào');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_recommendations');
    }
};
