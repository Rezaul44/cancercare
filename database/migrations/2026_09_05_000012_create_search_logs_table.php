<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_logs', function (Blueprint $table) {
            $table->id();
            $table->string('query', 255);
            $table->unsignedInteger('results_count')->default(0);
            $table->string('clicked_type', 30)->nullable();
            $table->unsignedBigInteger('clicked_id')->nullable();
            $table->string('session_hash', 64)->nullable(); // sha256(session id) — কোনো raw session/IP সংরক্ষণ হয় না
            $table->timestamp('created_at')->useCurrent();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_logs');
    }
};
