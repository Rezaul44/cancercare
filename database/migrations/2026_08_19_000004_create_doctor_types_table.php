<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_types', function (Blueprint $table) {
            $table->id();
            $table->string('key', 60)->unique();
            $table->string('label_bn', 100);
            $table->string('label_en', 100);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_types');
    }
};
