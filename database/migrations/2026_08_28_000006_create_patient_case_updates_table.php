<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_case_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_case_id')->constrained('patient_cases')->cascadeOnDelete();
            $table->text('note_bn');
            $table->date('update_date');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_public')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_case_updates');
    }
};
