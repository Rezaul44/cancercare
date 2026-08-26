<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guide_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guide_id')->constrained('guides')->cascadeOnDelete();
            $table->string('stage', 20);
            $table->string('title_bn', 160);
            $table->text('description_bn');
            $table->text('typical_treatment_bn');
            $table->string('duration_bn', 100);
            $table->unsignedInteger('cost_min')->nullable();
            $table->unsignedInteger('cost_max')->nullable();
            $table->string('severity_color', 30)->default('teal');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['guide_id', 'sort_order']);
            $table->unique(['guide_id', 'stage']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guide_stages');
    }
};
