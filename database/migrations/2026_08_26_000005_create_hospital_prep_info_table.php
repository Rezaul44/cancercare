<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospital_prep_info', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained('hospitals')->cascadeOnDelete();
            $table->enum('key', ['blood_bank', 'medicine_supply', 'attendant_policy', 'records_return']);
            $table->string('title_bn', 120);
            $table->text('description_bn');
            $table->string('flag_text_bn', 60)->nullable();
            $table->enum('flag_type', ['positive', 'warning', 'negative'])->default('warning');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospital_prep_info');
    }
};
