<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // "রোগীদের গল্প" — লিখিত টেক্সট গল্প। patient_cases (ধারা ৫, অনুদান-কেন্দ্রিক, ৩০ দিন পর
        // নিষ্ক্রিয়) থেকে সম্পূর্ণ আলাদা: এটি ডাক্তার প্রোফাইলের স্থায়ী বিশ্বাসযোগ্যতা-কন্টেন্ট, অনুদানের
        // সাথে সম্পর্কহীন এবং মেয়াদ ফুরায় না।
        Schema::create('doctor_patient_stories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cancer_type_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('stage', ['1', '2', '3', '4', 'unknown'])->nullable();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->string('patient_label_bn', 120);
            $table->smallInteger('year');
            $table->string('outcome_duration_bn', 60);
            $table->text('quote_bn');
            $table->string('then_bn', 160);
            $table->string('now_bn', 160);
            $table->boolean('is_name_changed')->default(false);
            $table->boolean('is_family_told')->default(false);
            $table->integer('sort_order')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_patient_stories');
    }
};
