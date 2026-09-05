<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospital_experience_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained('hospitals')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('hospital_experience_questions')->cascadeOnDelete();
            $table->integer('yes_count')->default(0);
            $table->integer('total_count')->default(0);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->boolean('is_published')->default(false);
            $table->timestamp('last_calculated_at')->nullable();
            $table->timestamps();

            $table->unique(['hospital_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospital_experience_summaries');
    }
};
