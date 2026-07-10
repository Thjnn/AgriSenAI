<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_quality_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('image_url')->comment('Ảnh nông dân chụp để AI quét');
            $table->enum('ripeness_level', ['xanh', 'vua_chin', 'chin', 'qua_chin'])
                ->nullable()->comment('Kết quả AI phân loại độ chín');
            $table->decimal('quality_score', 5, 2)->nullable()->comment('Điểm chất lượng 0-100 do AI chấm');
            $table->boolean('defect_detected')->default(false)->comment('AI phát hiện dấu hiệu hư hỏng/sâu bệnh');
            $table->text('defect_note')->nullable()->comment('Mô tả lỗi nếu có, do AI sinh ra');
            $table->json('raw_ai_response')->nullable()->comment('Toàn bộ phản hồi gốc từ Gemini Vision để truy vết');
            $table->timestamp('assessed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_quality_assessments');
    }
};
