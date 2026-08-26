<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // "রোগীদের গল্প" সেকশনের উপরের পিল/চিপ পরিসংখ্যান ("১২১ জন স্টেজ ৩ থেকে সুস্থ") — doctor_services,
        // doctor_philosophy_points-এর মতোই CCB নিজে লেখে (ডাক্তারের সাথে আলোচনা করে), কোনো স্বয়ংক্রিয়
        // হিসাব নয়।
        Schema::create('doctor_story_highlights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->string('label_bn', 120);
            $table->string('color_key', 30)->default('teal');
            $table->integer('sort_order')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_story_highlights');
    }
};
