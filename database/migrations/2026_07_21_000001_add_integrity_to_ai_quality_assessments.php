<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_quality_assessments', function (Blueprint $table) {
            $table->string('image_sha256', 64)->nullable()->after('image_url');
            $table->string('integrity_hash', 64)->nullable()->after('raw_ai_response');
        });
    }

    public function down(): void
    {
        Schema::table('ai_quality_assessments', function (Blueprint $table) {
            $table->dropColumn(['image_sha256', 'integrity_hash']);
        });
    }
};
