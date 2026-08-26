<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_rating_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('collected_by')->constrained('users');
            $table->enum('source', ['field_hospital', 'phone', 'whatsapp']);
            $table->string('collection_location', 120)->nullable();
            $table->string('patient_phone_hash', 64)->nullable();
            $table->enum('proof_type', ['prescription', 'receipt', 'none']);
            $table->string('proof_path', 255)->nullable();
            $table->json('answers');
            $table->text('free_comment_bn')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('collected_at');
            $table->timestamps();

            $table->unique(['doctor_id', 'patient_phone_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_rating_submissions');
    }
};
