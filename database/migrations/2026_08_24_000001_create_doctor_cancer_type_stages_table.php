<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ডাক্তার প্রোফাইলের "ম্যাচ ইঞ্জিন" (docs/prototypes/doctor_profile.html) — স্টেজ অনুযায়ী কেস
        // সংখ্যা ও ফলাফলের হার। শুধু doctor_cancer_type-এ যুক্ত ক্যান্সারের জন্যই সারি থাকে —
        // ডাক্তার যে ক্যান্সারে বিশেষজ্ঞ নন তার জন্য কোনো সারি লাগে না (ম্যাচ ইঞ্জিন সরাসরি "উপযুক্ত নন" দেখায়)।
        Schema::create('doctor_cancer_type_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cancer_type_id')->constrained()->cascadeOnDelete();
            $table->enum('stage', ['1', '2', '3', '4', 'unknown']);
            $table->unsignedInteger('case_count');
            $table->unsignedTinyInteger('success_rate_percent');
            $table->text('note_bn');

            $table->unique(['doctor_id', 'cancer_type_id', 'stage']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_cancer_type_stages');
    }
};
