<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('second_opinion_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_code', 20)->unique();
            $table->string('patient_name', 150);
            $table->unsignedTinyInteger('age');
            $table->foreignId('cancer_type_id')->constrained('cancer_types');
            $table->string('current_status', 20); // not_started, ongoing, completed, recurrence
            $table->text('treatments_done_bn')->nullable();
            $table->text('question_bn');
            $table->string('phone', 20);
            $table->foreignId('district_id')->constrained('districts');
            $table->foreignId('doctor_id')->constrained('doctors');
            $table->unsignedInteger('fee');
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->string('status', 20)->default('pending_payment'); // pending_payment, submitted, accepted, answered, refunded, cancelled
            $table->unsignedSmallInteger('expected_hours');
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();

            $table->index(['doctor_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('second_opinion_requests');
    }
};
