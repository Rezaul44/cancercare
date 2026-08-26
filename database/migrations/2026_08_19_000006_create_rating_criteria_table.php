<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rating_criteria', function (Blueprint $table) {
            $table->id();
            $table->string('key', 60)->unique();
            $table->string('label_bn', 100);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rating_criteria');
    }
};
