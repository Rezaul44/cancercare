<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * docs/CCB_database_schema.md ধারা ২ ও ১১: র‍্যাঙ্কিং ওয়েট ও হেল্পলাইন নম্বর কোডে
 * হার্ডকোড না করে settings টেবিলে — DoctorRankingService এই মান DB থেকে পড়ে
 * (docs/CCB_system_documentation.md ধারা ৮.১)।
 *
 * নিচের সংখ্যাগুলো শুরুর ডিফল্ট মাত্র — চূড়ান্ত ওয়েট ও আসল হেল্পলাইন নম্বর প্রকাশের
 * আগে নির্ধারণ করে settings ইউআই থেকে বদলাতে হবে।
 */
class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('settings')->updateOrInsert(
            ['key' => 'ranking.weights'],
            [
                'value' => json_encode([
                    'primary' => 40,      // cancer_type-এ is_primary ম্যাচ
                    'secondary' => 20,    // cancer_type-এ non-primary ম্যাচ
                    'district' => 25,     // চেম্বারের জেলা রোগীর জেলার সাথে মেলে
                    'rating' => 15,       // is_published rating summary থাকলে
                    'availability' => 10, // অ্যাপয়েন্টমেন্টের সহজলভ্যতা
                ]),
                'group' => 'ranking',
                'updated_by' => null,
                'updated_at' => now(),
            ]
        );

        DB::table('settings')->updateOrInsert(
            ['key' => 'helpline'],
            [
                'value' => json_encode([
                    'phone' => '09600-000000', // TODO: লঞ্চের আগে আসল হেল্পলাইন নম্বর বসাতে হবে
                    'hours' => 'সকাল ৯টা – রাত ৯টা, সপ্তাহে ৭ দিন',
                ]),
                'group' => 'general',
                'updated_by' => null,
                'updated_at' => now(),
            ]
        );
    }
}
