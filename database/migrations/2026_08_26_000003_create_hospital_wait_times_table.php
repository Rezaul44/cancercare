<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospital_wait_times', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained('hospitals')->cascadeOnDelete();
            $table->string('service_key', 60);
            $table->unsignedTinyInteger('min_weeks');
            $table->unsignedTinyInteger('max_weeks');
            $table->string('label_bn', 120);
            $table->enum('severity', ['short', 'medium', 'long'])->default('medium');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospital_wait_times');
    }
};
