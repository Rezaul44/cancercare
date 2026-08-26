<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guide_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guide_id')->constrained('guides')->cascadeOnDelete();
            $table->unsignedTinyInteger('step_no');
            $table->string('title_bn', 160);
            $table->text('description_bn');
            $table->string('when_label_bn', 120)->nullable();
            $table->string('urgency', 20)->default('normal');
            $table->json('items')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['guide_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guide_steps');
    }
};
