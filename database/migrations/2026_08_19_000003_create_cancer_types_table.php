<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cancer_types', function (Blueprint $table) {
            $table->id();
            $table->string('name_bn', 120);
            $table->string('name_en', 120);
            $table->string('slug', 120)->unique();
            $table->string('icon', 60);
            $table->string('color_key', 30);
            $table->text('short_description_bn');
            $table->enum('gender_bias', ['female', 'male', 'child', 'all'])->nullable();
            $table->boolean('is_common')->default(false);
            $table->unsignedInteger('doctor_count_cache')->default(0);
            $table->unsignedInteger('hospital_count_cache')->default(0);
            $table->boolean('guide_published')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cancer_types');
    }
};
