<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->morphs('payable'); // দ্বিতীয় মতামত ও ভবিষ্যতের অন্য সেবার জন্য পলিমরফিক
            $table->string('gateway', 20); // bkash, nagad, sslcommerz
            $table->unsignedInteger('amount');
            $table->unsignedInteger('gateway_fee')->nullable();
            $table->string('transaction_id', 100)->nullable();
            $table->string('status', 20)->default('initiated'); // initiated, success, failed, refunded
            $table->json('raw_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
