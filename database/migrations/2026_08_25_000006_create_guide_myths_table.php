<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guide_myths', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guide_id')->constrained('guides')->cascadeOnDelete();
            $table->text('myth_bn');
            $table->text('truth_bn');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['guide_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guide_myths');
    }
};
