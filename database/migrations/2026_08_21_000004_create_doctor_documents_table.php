<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['bmdc_certificate', 'degree', 'other']);
            $table->string('file_path', 255);
            $table->timestamp('uploaded_at');
            $table->boolean('is_private')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_documents');
    }
};
