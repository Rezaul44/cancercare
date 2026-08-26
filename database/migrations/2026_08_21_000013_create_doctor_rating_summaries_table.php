<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_rating_summaries', function (Blueprint $table) {
            $table->foreignId('doctor_id')->primary()->constrained()->cascadeOnDelete();
            $table->integer('total_count')->default(0);
            $table->json('criteria_scores');
            $table->decimal('overall_score', 2, 1)->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('last_calculated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_rating_summaries');
    }
};
