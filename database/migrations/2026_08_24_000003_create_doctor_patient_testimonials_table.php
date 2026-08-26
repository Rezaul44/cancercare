<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // "রোগীদের ভিডিও অভিজ্ঞতা" — doctor_videos (ডাক্তারের নিজের ভিডিও, intro/educational) থেকে
        // ইচ্ছাকৃতভাবে আলাদা টেবিল, কারণ এখানে বিষয় রোগী, ডাক্তার নয় (বেনামি বিবরণ, ফলাফল ব্যাজ,
        // ক্যান্সারের ধরন ও স্টেজ থাকে যা ডাক্তারের ভিডিওতে অর্থহীন)। রোগীর লিখিত সম্মতি ছাড়া সারি তৈরি হয় না।
        Schema::create('doctor_patient_testimonials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cancer_type_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('stage', ['1', '2', '3', '4', 'unknown'])->nullable();
            $table->string('outcome_bn', 80);
            $table->string('anonymized_label_bn', 120);
            $table->smallInteger('year');
            $table->string('video_url', 255);
            $table->unsignedInteger('duration_seconds');
            $table->string('thumbnail_color_key', 30)->default('teal');
            $table->integer('sort_order')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_patient_testimonials');
    }
};
