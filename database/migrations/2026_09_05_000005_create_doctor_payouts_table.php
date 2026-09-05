<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // CCB কমিশন নেয় না — তাই এখানে ইচ্ছাকৃতভাবে কোনো commission কলাম নেই।
        // শুধু গেটওয়ে চার্জ (gateway_fee) বাদ দিয়ে net_amount হিসাব হয়।
        Schema::create('doctor_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors');
            $table->date('period_start');
            $table->date('period_end');
            $table->unsignedInteger('request_count');
            $table->unsignedInteger('gross_amount');
            $table->unsignedInteger('gateway_fee');
            $table->unsignedInteger('net_amount');
            $table->string('status', 20)->default('pending'); // pending, paid
            $table->timestamp('paid_at')->nullable();
            $table->string('reference', 100)->nullable();
            $table->timestamps();

            $table->index(['doctor_id', 'period_start', 'period_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_payouts');
    }
};
