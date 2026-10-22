<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ho so lo xoai (theo gop y co Hang 05/10/2026):
 *   tao lo -> chup anh chuan -> AI goi y -> nguoi xac nhan (HITL) -> cong bo + QR.
 *
 * ai_suggestions va lot_audit_logs la CHI THEM (append-only): phan tich lai =
 * them dong moi. Chan sua/xoa o model (xem AuditLog, AiSuggestion).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lots', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique()->comment('vd XL-261022-AB12, in tren tem QR');
            $table->string('supplier', 150)->comment('nha vuon / nguon cung');
            $table->string('variety', 100)->nullable()->comment('giong, nguoi nhap');
            $table->string('purpose', 10)->comment('an_xanh / an_chin');
            $table->decimal('weight_kg', 10, 2);
            $table->date('harvest_date');
            $table->string('status', 12)->default('draft')
                ->comment('draft / analyzing / analyzed / confirmed / published');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('lot_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lot_id')->constrained()->cascadeOnDelete();
            $table->string('path')->comment('anh DA chuan hoa 768x768, disk public');
            $table->char('sha256', 64);
            $table->string('view', 10)->comment('toan_qua / be_mat / cuong / khac');
            $table->json('quality')->comment('{ok, issues[], blur, brightness, width, height}');
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('ai_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lot_id')->constrained()->cascadeOnDelete();
            $table->string('model', 60);
            $table->string('prompt_version', 20);
            $table->json('photo_ids');
            $table->json('result')->nullable()->comment('NULL neu loi');
            $table->text('error')->nullable();
            $table->unsignedInteger('latency_ms')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // Ket qua nguoi xac nhan theo TUNG tieu chi = mot nhan du lieu.
        Schema::create('verified_labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lot_id')->constrained()->cascadeOnDelete();
            $table->foreignId('suggestion_id')->nullable()->constrained('ai_suggestions')
                ->comment('NULL = nhap thu cong');
            $table->string('criterion', 12)->comment('color / surface / shape / uniformity / ripeness');
            $table->string('decision', 8)->comment('accept / edit / reject / manual');
            $table->json('ai_value')->nullable();
            $table->json('final_value')->nullable()->comment('NULL khi reject');
            $table->string('note', 500)->nullable();
            $table->foreignId('confirmed_by')->constrained('users');
            $table->timestamp('confirmed_at');
        });

        // Nhat ky noi chuoi bam theo tung lo: hash = sha256(prev_hash . payload).
        Schema::create('lot_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lot_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->comment('NULL = he thong');
            $table->string('action', 20)->comment('created / photo_added / ai_suggested / confirmed / published');
            $table->text('payload')->comment('JSON sort key, dung chuoi da bam');
            $table->char('hash', 64);
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lot_audit_logs');
        Schema::dropIfExists('verified_labels');
        Schema::dropIfExists('ai_suggestions');
        Schema::dropIfExists('lot_photos');
        Schema::dropIfExists('lots');
    }
};
