<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_case_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_case_id')->constrained('patient_cases')->cascadeOnDelete();
            $table->string('type', 30); // bkash, nagad, rocket, bank
            $table->string('account_number', 60);
            $table->string('account_name', 150);
            $table->string('bank_name', 120)->nullable();
            $table->string('branch', 120)->nullable();
            $table->boolean('name_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_case_accounts');
    }
};
