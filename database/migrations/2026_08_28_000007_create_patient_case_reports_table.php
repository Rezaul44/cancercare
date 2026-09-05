<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_case_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_case_id')->constrained('patient_cases')->cascadeOnDelete();
            $table->string('reporter_phone', 30)->nullable();
            $table->string('reason', 150);
            $table->text('details')->nullable();
            $table->string('status', 30)->default('new'); // new, reviewing, resolved, dismissed
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_case_reports');
    }
};
