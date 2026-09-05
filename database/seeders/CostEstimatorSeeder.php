<?php

namespace Database\Seeders;

use App\Models\CancerType;
use App\Models\CostBaseRate;
use App\Models\CostIndirectRate;
use App\Models\CostMultiplier;
use App\Models\CostPhaseTemplate;
use Illuminate\Database\Seeder;

class CostEstimatorSeeder extends Seeder
{
    public function run(): void
    {
        $cancerTypeMap = [
            'breast' => 'breast-cancer',
            'lung' => 'lung-cancer',
            'cervical' => 'cervical-cancer',
            'blood' => 'blood-cancer',
            'stomach' => 'stomach-cancer',
            'oral' => 'oral-cancer',
            'colon' => 'colon-cancer',
            'prostate' => 'prostate-cancer',
        ];

        $baseData = [
            'breast' => ['diag' => 14000, 'surgery' => 32000, 'chemo' => 48000, 'radiation' => 22000, 'targeted' => 180000, 'months' => 8],
            'lung' => ['diag' => 20000, 'surgery' => 45000, 'chemo' => 62000, 'radiation' => 26000, 'targeted' => 240000, 'months' => 9],
            'cervical' => ['diag' => 12000, 'surgery' => 30000, 'chemo' => 40000, 'radiation' => 28000, 'targeted' => 0, 'months' => 7],
            'blood' => ['diag' => 24000, 'surgery' => 0, 'chemo' => 110000, 'radiation' => 18000, 'targeted' => 200000, 'months' => 14],
            'stomach' => ['diag' => 14000, 'surgery' => 48000, 'chemo' => 52000, 'radiation' => 20000, 'targeted' => 150000, 'months' => 8],
            'oral' => ['diag' => 13000, 'surgery' => 42000, 'chemo' => 38000, 'radiation' => 30000, 'targeted' => 0, 'months' => 7],
            'colon' => ['diag' => 16000, 'surgery' => 46000, 'chemo' => 56000, 'radiation' => 18000, 'targeted' => 170000, 'months' => 8],
            'prostate' => ['diag' => 17000, 'surgery' => 44000, 'chemo' => 40000, 'radiation' => 28000, 'targeted' => 0, 'months' => 9],
        ];

        // 1. Seed Base Rates
        foreach ($baseData as $shortKey => $rates) {
            $slug = $cancerTypeMap[$shortKey];
            $cancerType = CancerType::where('slug', $slug)->first();
            if (! $cancerType) {
                continue;
            }

            $services = [
                'diagnosis' => $rates['diag'],
                'surgery' => $rates['surgery'],
                'chemo' => $rates['chemo'],
                'radiation' => $rates['radiation'],
                'targeted' => $rates['targeted'],
            ];

            foreach ($services as $serviceKey => $amount) {
                CostBaseRate::updateOrCreate(
                    [
                        'cancer_type_id' => $cancerType->id,
                        'service_key' => $serviceKey,
                    ],
                    [
                        'govt_amount' => $amount,
                        'default_months' => $rates['months'],
                        'is_applicable' => $amount > 0,
                    ]
                );
            }
        }

        // 2. Seed Multipliers
        $multipliers = [
            // Stage
            ['group' => 'stage', 'key' => '1', 'multiplier' => 0.72, 'label_bn' => 'স্টেজ ১', 'sort_order' => 1],
            ['group' => 'stage', 'key' => '2', 'multiplier' => 1.00, 'label_bn' => 'স্টেজ ২', 'sort_order' => 2],
            ['group' => 'stage', 'key' => '3', 'multiplier' => 1.35, 'label_bn' => 'স্টেজ ৩', 'sort_order' => 3],
            ['group' => 'stage', 'key' => '4', 'multiplier' => 1.55, 'label_bn' => 'স্টেজ ৪', 'sort_order' => 4],

            // Hospital Type
            ['group' => 'hospital_type', 'key' => 'govt', 'multiplier' => 1.00, 'label_bn' => 'সরকারি হাসপাতালে', 'sort_order' => 1],
            ['group' => 'hospital_type', 'key' => 'npo', 'multiplier' => 1.90, 'label_bn' => 'অলাভজনক হাসপাতালে', 'sort_order' => 2],
            ['group' => 'hospital_type', 'key' => 'priv', 'multiplier' => 4.40, 'label_bn' => 'বেসরকারি হাসপাতালে', 'sort_order' => 3],

            // Distance
            ['group' => 'distance', 'key' => 'local', 'multiplier' => 0.35, 'label_bn' => 'ঢাকার ভেতরে', 'sort_order' => 1],
            ['group' => 'distance', 'key' => 'near', 'multiplier' => 0.70, 'label_bn' => 'ঢাকার কাছাকাছি (৫০–১৫০ কিমি)', 'sort_order' => 2],
            ['group' => 'distance', 'key' => 'far', 'multiplier' => 1.00, 'label_bn' => 'দূরের জেলা (১৫০+ কিমি)', 'sort_order' => 3],
        ];

        foreach ($multipliers as $item) {
            CostMultiplier::updateOrCreate(
                ['group' => $item['group'], 'key' => $item['key']],
                $item
            );
        }

        // 3. Seed Indirect Rates
        $indirectRates = [
            ['key' => 'travel', 'label_bn' => 'যাতায়াত', 'base_amount' => 1400, 'unit' => 'per_trip', 'percent_value' => null, 'sort_order' => 1],
            ['key' => 'stay', 'label_bn' => 'থাকার খরচ', 'base_amount' => 900, 'unit' => 'per_trip', 'percent_value' => null, 'sort_order' => 2],
            ['key' => 'food', 'label_bn' => 'খাওয়া', 'base_amount' => 350, 'unit' => 'per_trip', 'percent_value' => null, 'sort_order' => 3],
            ['key' => 'outside_medicine', 'label_bn' => 'বাইরে থেকে ওষুধ', 'base_amount' => 0, 'unit' => 'percent_of_direct', 'percent_value' => 0.18, 'sort_order' => 4],
            ['key' => 'income_loss_far', 'label_bn' => 'আয় বন্ধ থাকা (দূরবর্তী)', 'base_amount' => 9000, 'unit' => 'per_month', 'percent_value' => null, 'sort_order' => 5],
            ['key' => 'income_loss_near', 'label_bn' => 'আয় বন্ধ থাকা (কাছাকাছি)', 'base_amount' => 6000, 'unit' => 'per_month', 'percent_value' => null, 'sort_order' => 6],
            ['key' => 'misc', 'label_bn' => 'অন্যান্য', 'base_amount' => 400, 'unit' => 'per_trip', 'percent_value' => null, 'sort_order' => 7],
        ];

        foreach ($indirectRates as $item) {
            CostIndirectRate::updateOrCreate(
                ['key' => $item['key']],
                $item
            );
        }

        // 4. Seed Phase Templates
        $phaseTemplates = [
            [
                'service_key' => 'diagnosis',
                'phase_title_bn' => 'নির্ণয় ও পরীক্ষা',
                'when_bn' => 'প্রথম ২–৪ সপ্তাহ',
                'breakdown' => [
                    ['বায়োপসি ও প্যাথলজি', 0.45],
                    ['ইমেজিং (CT/আল্ট্রাসনো)', 0.35],
                    ['রক্ত পরীক্ষা ও ডাক্তারের ফি', 0.20],
                ],
                'sort_order' => 1,
            ],
            [
                'service_key' => 'surgery',
                'phase_title_bn' => 'অপারেশন',
                'when_bn' => '১–২ মাসের মধ্যে',
                'breakdown' => [
                    ['অপারেশন ও ওটি চার্জ', 0.70],
                    ['ভর্তি ও শয্যা', 0.20],
                    ['ওষুধ ও ড্রেসিং', 0.10],
                ],
                'sort_order' => 2,
            ],
            [
                'service_key' => 'chemo',
                'phase_title_bn' => 'কেমোথেরাপি',
                'when_bn' => '৪–৬ মাস · ৬–৮টি সাইকেল',
                'breakdown' => [
                    ['কেমো ওষুধ', 0.60],
                    ['ডে-কেয়ার ও প্রশাসন', 0.25],
                    ['পার্শ্বপ্রতিক্রিয়ার ওষুধ', 0.15],
                ],
                'sort_order' => 3,
            ],
            [
                'service_key' => 'radiation',
                'phase_title_bn' => 'রেডিওথেরাপি',
                'when_bn' => '৫–৭ সপ্তাহ · ২৫–৩৩টি সেশন',
                'breakdown' => [
                    ['রেডিয়েশন সেশন', 0.80],
                    ['প্ল্যানিং ও সিমুলেশন', 0.20],
                ],
                'sort_order' => 4,
            ],
            [
                'service_key' => 'targeted',
                'phase_title_bn' => 'টার্গেটেড থেরাপি',
                'when_bn' => '৬–১২ মাস',
                'breakdown' => [
                    ['ওষুধ (প্রায়ই বাইরে থেকে)', 0.90],
                    ['প্রয়োগ ও পর্যবেক্ষণ', 0.10],
                ],
                'sort_order' => 5,
            ],
            [
                'service_key' => 'followup',
                'phase_title_bn' => 'ফলো-আপ (প্রথম বছর)',
                'when_bn' => 'চিকিৎসার পর',
                'breakdown' => [
                    ['নিয়মিত পরীক্ষা ও স্ক্যান', 0.40],
                    ['ডাক্তারের ফি', 0.15],
                ],
                'sort_order' => 6,
            ],
        ];

        foreach ($phaseTemplates as $item) {
            CostPhaseTemplate::updateOrCreate(
                ['service_key' => $item['service_key'], 'cancer_type_id' => null],
                $item
            );
        }
    }
}
