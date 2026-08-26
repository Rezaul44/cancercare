<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->constrained();
            $table->string('name_bn', 80);
            $table->string('name_en', 80);
            $table->string('slug', 80)->unique();
            $table->enum('distance_tier', ['local', 'near', 'far']);
            $table->boolean('has_cancer_center')->default(false);

            $table->index(['division_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('districts');
    }
};
