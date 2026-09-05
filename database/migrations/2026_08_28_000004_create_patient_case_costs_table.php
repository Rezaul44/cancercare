<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_case_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_case_id')->constrained('patient_cases')->cascadeOnDelete();
            $table->string('item_bn', 150);
            $table->unsignedInteger('amount');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_case_costs');
    }
};
