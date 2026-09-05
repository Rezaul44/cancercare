<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cost_multipliers', function (Blueprint $table) {
            $table->id();
            $table->string('group', 50); // stage, hospital_type, distance
            $table->string('key', 50);   // 1, 2, 3, 4 | govt, npo, priv | local, near, far
            $table->decimal('multiplier', 5, 2);
            $table->string('label_bn', 100);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['group', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_multipliers');
    }
};
