<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_applications', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 150);
            $table->string('bmdc_number', 40);
            $table->string('phone', 20);
            $table->string('email', 191)->nullable();
            $table->string('photo_path', 255)->nullable();
            $table->json('degrees');
            $table->json('timeline');
            $table->json('doctor_type_ids');
            $table->json('cancer_type_ids');
            $table->json('chambers');
            $table->json('extra_services');
            $table->string('preferred_call_time', 40)->nullable();
            $table->string('preferred_call_day', 40)->nullable();
            $table->json('declarations');
            $table->enum('status', ['submitted', 'under_review', 'call_scheduled', 'approved', 'rejected'])
                ->default('submitted');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('review_note')->nullable();
            // doctors টেবিল এই migration-এর পরে তৈরি হয় (ধারা ১৪ ক্রম) — চক্রাকার নির্ভরতা এড়াতে
            // FK constraint পরের migration-এ (add_doctor_id_foreign_to_doctor_applications_table)।
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_applications');
    }
};
