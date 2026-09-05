<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospital_experience_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained('hospitals')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('hospital_experience_questions')->cascadeOnDelete();
            $table->boolean('answer');
            $table->foreignId('collected_by')->constrained('users');
            $table->enum('source', ['field_hospital', 'phone', 'whatsapp'])->default('field_hospital');
            $table->date('collected_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospital_experience_responses');
    }
};
