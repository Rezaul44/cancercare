<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospital_capabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained('hospitals')->cascadeOnDelete();
            $table->foreignId('capability_id')->constrained('capabilities')->cascadeOnDelete();
            $table->enum('status', ['available', 'limited', 'not_available'])->default('not_available');
            $table->text('detail_bn')->nullable();
            $table->unsignedTinyInteger('machine_count')->nullable();
            $table->date('last_checked_at')->nullable();
            $table->timestamps();

            $table->unique(['hospital_id', 'capability_id']);
            $table->index(['capability_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospital_capabilities');
    }
};
