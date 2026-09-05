<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospital_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained('hospitals')->cascadeOnDelete();
            $table->string('video_url', 255);
            $table->enum('platform', ['youtube', 'facebook'])->default('youtube');
            $table->string('title_bn', 200);
            $table->text('description_bn')->nullable();
            $table->integer('duration_seconds')->default(0);
            $table->string('produced_by', 80)->default('CancerCare Bangladesh');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospital_videos');
    }
};
