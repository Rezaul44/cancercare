<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cancer_type_id')->constrained()->cascadeOnDelete();
            $table->string('title_bn', 160);
            $table->text('description_bn');
            $table->string('badge_text_bn', 80)->nullable();
            $table->string('badge_color', 30);
            $table->string('icon', 60);
            $table->integer('sort_order')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_services');
    }
};
