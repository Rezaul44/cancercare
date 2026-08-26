<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctor_applications', function (Blueprint $table) {
            // schema doc-এ BMDC সনদের path রাখার জন্য কোনো কলাম নেই (শুধু photo_path আছে,
            // degrees json ধাপ ২-নির্দিষ্ট) — ইউজারের সিদ্ধান্তে এই কলাম যোগ করা হলো।
            $table->string('bmdc_certificate_path', 255)->nullable()->after('photo_path');
        });
    }

    public function down(): void
    {
        Schema::table('doctor_applications', function (Blueprint $table) {
            $table->dropColumn('bmdc_certificate_path');
        });
    }
};
