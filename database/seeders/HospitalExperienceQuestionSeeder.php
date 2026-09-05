<?php

namespace Database\Seeders;

use App\Models\HospitalExperienceQuestion;
use Illuminate\Database\Seeder;

class HospitalExperienceQuestionSeeder extends Seeder
{
    private const QUESTIONS = [
        [
            'key' => 'wait_time_accurate',
            'label_bn' => 'অপেক্ষার সময় যা বলা হয়েছিল তাই লেগেছে',
            'sort_order' => 1,
            'is_active' => true,
        ],
        [
            'key' => 'medicine_available',
            'label_bn' => 'হাসপাতালের ভেতর প্রয়োজনীয় ওষুধ পাওয়া গেছে',
            'sort_order' => 2,
            'is_active' => true,
        ],
        [
            'key' => 'machine_working',
            'label_bn' => 'রেডিওথেরাপি ও স্ক্যান মেশিন সচল ছিল',
            'sort_order' => 3,
            'is_active' => true,
        ],
        [
            'key' => 'doctor_explained',
            'label_bn' => 'ডাক্তার মনোযোগ দিয়ে চিকিৎসা পদ্ধতি বুঝিয়েছেন',
            'sort_order' => 4,
            'is_active' => true,
        ],
        [
            'key' => 'female_doctor_available',
            'label_bn' => 'নারী ডাক্তার বা নারী স্বাস্থ্যকর্মী চেয়ে পাওয়া গেছে',
            'sort_order' => 5,
            'is_active' => true,
        ],
        [
            'key' => 'cost_as_told',
            'label_bn' => 'খরচের হিসাব শুরুতে যা বলা হয়েছিল তার বেশি লাগেনি',
            'sort_order' => 6,
            'is_active' => true,
        ],
    ];

    public function run(): void
    {
        foreach (self::QUESTIONS as $q) {
            HospitalExperienceQuestion::updateOrInsert(
                ['key' => $q['key']],
                [
                    'label_bn' => $q['label_bn'],
                    'sort_order' => $q['sort_order'],
                    'is_active' => $q['is_active'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
