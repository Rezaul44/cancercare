<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctor_applications', function (Blueprint $table) {
            // StoreDoctorApplicationRequest এ validate হয় কিন্তু submit() আগে save করত না —
            // অনুমোদনের সময় doctors.experience_years / doctors.current_position_bn পূরণ করতে দরকার।
            $table->tinyInteger('experience_years')->unsigned()->nullable()->after('bmdc_certificate_path');
            $table->string('current_position', 200)->nullable()->after('experience_years');

            // "যাচাই সম্পন্ন" action-এর BMDC/ডিগ্রি checklist এখানে রাখা হয়, table-এর বাকি
            // json কলামগুলোর মতোই।
            $table->json('verification_checklist')->nullable()->after('review_note');
        });
    }

    public function down(): void
    {
        Schema::table('doctor_applications', function (Blueprint $table) {
            $table->dropColumn(['experience_years', 'current_position', 'verification_checklist']);
        });
    }
};
