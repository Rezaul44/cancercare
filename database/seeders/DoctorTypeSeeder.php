<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * ৬টি doctor_type। docs/CCB_database_schema.md ধারা ২: "medical / surgical /
 * radiation / hemato / gynecologic / pediatric oncologist"।
 */
class DoctorTypeSeeder extends Seeder
{
    /**
     * @var list<array{key: string, label_bn: string, label_en: string}>
     */
    private const DOCTOR_TYPES = [
        ['key' => 'medical_oncologist', 'label_bn' => 'মেডিকেল অনকোলজিস্ট', 'label_en' => 'Medical Oncologist'],
        ['key' => 'surgical_oncologist', 'label_bn' => 'সার্জিক্যাল অনকোলজিস্ট', 'label_en' => 'Surgical Oncologist'],
        ['key' => 'radiation_oncologist', 'label_bn' => 'রেডিয়েশন অনকোলজিস্ট', 'label_en' => 'Radiation Oncologist'],
        ['key' => 'hemato_oncologist', 'label_bn' => 'হেমাটো-অনকোলজিস্ট', 'label_en' => 'Hemato-Oncologist'],
        ['key' => 'gynecologic_oncologist', 'label_bn' => 'গাইনোকোলজিক অনকোলজিস্ট', 'label_en' => 'Gynecologic Oncologist'],
        ['key' => 'pediatric_oncologist', 'label_bn' => 'পেডিয়াট্রিক অনকোলজিস্ট', 'label_en' => 'Pediatric Oncologist'],
    ];

    public function run(): void
    {
        foreach (self::DOCTOR_TYPES as $type) {
            DB::table('doctor_types')->updateOrInsert(
                ['key' => $type['key']],
                ['label_bn' => $type['label_bn'], 'label_en' => $type['label_en']]
            );
        }
    }
}
