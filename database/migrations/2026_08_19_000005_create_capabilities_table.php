<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('capabilities', function (Blueprint $table) {
            $table->id();
            $table->string('key', 60)->unique();
            $table->string('label_bn', 100);
            $table->string('icon', 60);
            $table->integer('sort_order')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('capabilities');
    }
};
