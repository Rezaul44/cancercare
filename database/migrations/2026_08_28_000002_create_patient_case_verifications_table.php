<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_case_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_case_id')->constrained('patient_cases')->cascadeOnDelete();
            $table->string('step', 40); // documents, hospital_confirm, identity, field_meeting
            $table->string('status', 30)->default('pending'); // pending, done, failed
            $table->text('note_bn')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['patient_case_id', 'step']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_case_verifications');
    }
};
