<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('second_opinion_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('second_opinion_requests')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('doctors');
            $table->text('response_bn');
            $table->boolean('call_made')->default(false);
            $table->text('call_note')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('second_opinion_responses');
    }
};
