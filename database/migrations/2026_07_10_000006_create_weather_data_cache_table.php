<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weather_data_cache', function (Blueprint $table) {
            $table->id();
            $table->string('region', 150);
            $table->date('date');
            $table->decimal('temperature', 5, 2)->nullable()->comment('Độ C');
            $table->decimal('humidity', 5, 2)->nullable()->comment('% độ ẩm');
            $table->string('rain_forecast', 100)->nullable()->comment('Mô tả dự báo mưa');
            $table->timestamp('fetched_at')->useCurrent();
            $table->unique(['region', 'date'], 'uq_region_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_data_cache');
    }
};
