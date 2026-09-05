<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('helpline_logs', function (Blueprint $table) {
            $table->id();
            $table->string('channel', 20); // phone, whatsapp
            $table->string('caller_name', 150)->nullable();
            $table->string('caller_phone', 20);
            $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->foreignId('cancer_type_id')->nullable()->constrained('cancer_types')->nullOnDelete();
            $table->string('topic', 30); // report_help, find_doctor, cost_query, financial_aid, case_application, complaint, other
            $table->text('summary_bn');
            $table->string('outcome', 30); // resolved, referred, follow_up_needed, case_created
            $table->foreignId('linked_case_id')->nullable()->constrained('patient_cases')->nullOnDelete();
            $table->foreignId('handled_by')->constrained('users');
            $table->date('follow_up_at')->nullable();
            $table->unsignedSmallInteger('call_duration_minutes')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('created_at');
            $table->index('topic');
            $table->index('follow_up_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('helpline_logs');
    }
};
