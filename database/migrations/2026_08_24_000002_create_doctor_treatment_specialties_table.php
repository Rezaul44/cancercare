<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // একই "ম্যাচ ইঞ্জিন"-এর দ্বিতীয় অংশ — প্রতিটি চিকিৎসা-ধরন (অপারেশন/কেমো/রেডিয়েশন/হরমোন)
        // ডাক্তার নিজে দেন (provides) নাকি অন্য বিশেষজ্ঞের কাছে পাঠান (refers)। স্টেজ-নিরপেক্ষ, তাই
        // doctor_cancer_type_stages থেকে আলাদা টেবিল।
        Schema::create('doctor_treatment_specialties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cancer_type_id')->constrained()->cascadeOnDelete();
            $table->enum('treatment_key', ['surgery', 'chemo', 'radiation', 'hormone', 'unknown']);
            $table->enum('role', ['provides', 'refers']);
            $table->text('note_bn');

            $table->unique(['doctor_id', 'cancer_type_id', 'treatment_key'], 'doctor_treatment_specialties_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_treatment_specialties');
    }
};
