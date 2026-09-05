<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // একজন User-এর সাথে সর্বোচ্চ একটি Doctor রেকর্ড লিঙ্ক হতে পারবে — ডাক্তার পোর্টাল
        // লগইনের জন্য User::doctor() one-to-one সম্পর্কটি নির্ভরযোগ্য হতে হলে এটা দরকার।
        Schema::table('doctors', function (Blueprint $table) {
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
        });
    }
};
