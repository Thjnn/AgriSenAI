<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_price_reference', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories');
            $table->string('region', 150)->nullable();
            $table->decimal('price', 12, 2)->comment('Giá tham khảo trên thị trường');
            $table->date('price_date');
            $table->string('source')->nullable()->comment('Nguồn dữ liệu giá');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_price_reference');
    }
};
