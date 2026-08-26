<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guide_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guide_id')->constrained('guides')->cascadeOnDelete();
            $table->string('video_url', 255);
            $table->string('platform', 30)->default('youtube');
            $table->string('title_bn', 200);
            $table->text('description_bn')->nullable();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['guide_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guide_videos');
    }
};
