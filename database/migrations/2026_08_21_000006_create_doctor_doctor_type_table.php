<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_doctor_type', function (Blueprint $table) {
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_type_id')->constrained()->cascadeOnDelete();

            $table->primary(['doctor_id', 'doctor_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_doctor_type');
    }
};
