<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospital_doctor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained('hospitals')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->string('schedule_note_bn', 200)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['hospital_id', 'doctor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospital_doctor');
    }
};
