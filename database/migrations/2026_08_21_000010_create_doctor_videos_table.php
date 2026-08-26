<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['intro', 'educational']);
            $table->enum('platform', ['youtube', 'facebook']);
            $table->string('video_url', 255);
            $table->string('thumbnail_path', 255)->nullable();
            $table->string('title_bn', 200);
            $table->text('description_bn')->nullable();
            $table->integer('duration_seconds');
            $table->integer('view_count')->nullable();
            $table->string('produced_by', 80)->default('Doctor Pro Media');
            $table->boolean('is_paid_production')->default(false);
            $table->integer('sort_order')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_videos');
    }
};
