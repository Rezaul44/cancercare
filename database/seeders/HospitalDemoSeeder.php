<?php

namespace Database\Seeders;

use App\Enums\HospitalCapabilityStatus;
use App\Enums\HospitalFlagType;
use App\Enums\HospitalPracticalKey;
use App\Enums\HospitalPrepKey;
use App\Enums\HospitalStatus;
use App\Enums\HospitalType;
use App\Enums\HospitalWaitTimeSeverity;
use App\Models\Capability;
use App\Models\District;
use App\Models\Doctor;
use App\Models\Hospital;
use App\Models\HospitalCapability;
use App\Models\HospitalCost;
use App\Models\HospitalPracticalInfo;
use App\Models\HospitalPrepInfo;
use App\Models\HospitalVideo;
use App\Models\HospitalWaitTime;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HospitalDemoSeeder extends Seeder
{
    public function run(): void
    {
        $dhakaDistrict = District::where('slug', 'dhaka')->first();
        if (! $dhakaDistrict) {
            return;
        }

        $capabilitiesByKey = Capability::all()->keyBy('key');

        // 1. NICRH (Govt)
        $nicrh = Hospital::updateOrInsert(
            ['slug' => 'nicrh-dhaka'],
            [
                'name_bn' => 'জাতীয় ক্যান্সার গবেষণা ইনস্টিটিউট ও হাসপাতাল (NICRH)',
                'name_en' => 'National Institute of Cancer Research & Hospital',
                'type' => HospitalType::Govt->value,
                'district_id' => $dhakaDistrict->id,
                'address_bn' => 'টিবি গেট, মহাখালী, ঢাকা-১২১২',
                'latitude' => 23.7788,
                'longitude' => 90.4045,
                'phone' => '০২-৯৮৯৮৬০১',
                'established_year' => 1982,
                'bed_count' => 500,
                'oncologist_count' => 45,
                'outdoor_fee' => 30,
                'emergency_24h' => true,
                'annual_patients' => '২,৫০,০০০+',
                'cover_photo_path' => 'hospitals/nicrh.jpg',
                'description_bn' => 'বাংলাদেশের ক্যান্সার চিকিৎসায় একমাত্র পূর্ণাঙ্গ সরকারি বিশেষায়িত ইনস্টিটিউট ও টারশিয়ারি হাসপাতাল। সর্বাধুনিক রেডিওথেরাপি, ডে-কেয়ার কেমোথেরাপি ও অনকো-সার্জারি সুবিধা এখানে বিদ্যমান।',
                'last_verified_at' => now(),
                'status' => HospitalStatus::Published->value,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
        $nicrhHospital = Hospital::where('slug', 'nicrh-dhaka')->first();

        // 2. Ahsania Mission (NPO)
        Hospital::updateOrInsert(
            ['slug' => 'ahsania-mission-cancer-hospital'],
            [
                'name_bn' => 'আহসানিয়া মিশন ক্যান্সার ও জেনারেল হাসপাতাল',
                'name_en' => 'Ahsania Mission Cancer & General Hospital',
                'type' => HospitalType::Npo->value,
                'district_id' => $dhakaDistrict->id,
                'address_bn' => 'প্লট ৩-১০, সেক্টর ১০, উত্তরা, ঢাকা-১২৩০',
                'latitude' => 23.8821,
                'longitude' => 90.3879,
                'phone' => '০৯৬১৩-০১০১০১',
                'established_year' => 2001,
                'bed_count' => 450,
                'oncologist_count' => 28,
                'outdoor_fee' => 500,
                'emergency_24h' => true,
                'annual_patients' => '৮০,০০০+',
                'cover_photo_path' => 'hospitals/ahsania.jpg',
                'description_bn' => 'ঢাকা আহ্ছানিয়া মিশন কর্তৃক প্রতিষ্ঠিত সর্ববৃহৎ অলাভজনক ও জনহিতকর ক্যান্সার হাসপাতাল। অসচ্ছল রোগীদের জন্য যাকাত ও অনুদান তহবিলের মাধ্যমে ভর্তুকিতে চিকিৎসা দেওয়া হয়।',
                'last_verified_at' => now(),
                'status' => HospitalStatus::Published->value,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
        $ahsaniaHospital = Hospital::where('slug', 'ahsania-mission-cancer-hospital')->first();

        // 3. Delta Hospital (Private)
        Hospital::updateOrInsert(
            ['slug' => 'delta-hospital-dhaka'],
            [
                'name_bn' => 'ডেল্টা হাসপাতাল লিমিটেড',
                'name_en' => 'Delta Hospital Limited',
                'type' => HospitalType::Private->value,
                'district_id' => $dhakaDistrict->id,
                'address_bn' => '২৬/২, প্রিন্সিপাল আবুল কাশেম রোড, মিরপুর-১, ঢাকা-১২১৬',
                'latitude' => 23.7994,
                'longitude' => 90.3541,
                'phone' => '০২-৮০৩১৩৭৮',
                'established_year' => 1989,
                'bed_count' => 250,
                'oncologist_count' => 20,
                'outdoor_fee' => 1000,
                'emergency_24h' => true,
                'annual_patients' => '৪৫,০০০+',
                'cover_photo_path' => 'hospitals/delta.jpg',
                'description_bn' => 'বেসরকারি খাতের অন্যতম প্রাচীন ও প্রতিষ্ঠিত ক্যান্সার চিকিৎসা কেন্দ্র। অত্যাধুনিক লিনিয়ার এক্সিলারেটর ও কেমোথেরাপি ডে-কেয়ার সুবিধাসম্পন্ন।',
                'last_verified_at' => now(),
                'status' => HospitalStatus::Published->value,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
        $deltaHospital = Hospital::where('slug', 'delta-hospital-dhaka')->first();

        // Populate NICRH capabilities & data
        if ($nicrhHospital) {
            $this->seedCapabilitiesForNicrh($nicrhHospital, $capabilitiesByKey);
            $this->seedWaitTimes($nicrhHospital);
            $this->seedCosts($nicrhHospital);
            $this->seedPrepInfo($nicrhHospital);
            $this->seedPracticalInfo($nicrhHospital);
            $this->seedVideos($nicrhHospital);
            $this->linkDoctors($nicrhHospital);
        }
    }

    private function seedCapabilitiesForNicrh(Hospital $hospital, $capabilitiesByKey): void
    {
        $matrix = [
            'radiotherapy' => ['status' => HospitalCapabilityStatus::Available, 'detail' => '৩টি লিনিয়ার অ্যাক্সিলারেটর ও ১টি ব্র্যাকিথেরাপি মেশিন সচল', 'count' => 4],
            'chemotherapy' => ['status' => HospitalCapabilityStatus::Available, 'detail' => '৫০ বেডের সুসজ্জিত ডে-কেয়ার কেমোথেরাপি ইউনিট', 'count' => 50],
            'cancer_surgery' => ['status' => HospitalCapabilityStatus::Available, 'detail' => 'অনকো-সার্জারির জন্য ৬টি আধুনিক মডিউলার অপারেশন থিয়েটার', 'count' => 6],
            'bmt' => ['status' => HospitalCapabilityStatus::Limited, 'detail' => 'সীমিত পরিসরে অটোলোগাস বিএমটি চালু রয়েছে', 'count' => 1],
            'pediatric_unit' => ['status' => HospitalCapabilityStatus::Available, 'detail' => 'শিশু ক্যান্সার রোগীদের জন্য আলাদা ৩০ বেডের ওয়ার্ড', 'count' => 30],
            'palliative_care' => ['status' => HospitalCapabilityStatus::Available, 'detail' => 'প্যালিয়েটিভ কেয়ার ইউনিট ও হোম-কেয়ার টিম', 'count' => 20],
            'pathology_lab' => ['status' => HospitalCapabilityStatus::Available, 'detail' => 'হিস্টোপ্যাথলজি ও ইমিউনোহিস্টোকেমিস্ট্রি (IHC) ল্যাব', 'count' => 2],
            'pet_ct' => ['status' => HospitalCapabilityStatus::Available, 'detail' => '১টি সর্বাধুনিক পিইটি-সিটি স্ক্যানার মেশিন', 'count' => 1],
            'targeted_therapy' => ['status' => HospitalCapabilityStatus::Available, 'detail' => 'বায়োলজিক্যাল ও ইমিউনোথেরাপি প্রয়োগ সুবিধা', 'count' => 1],
            'female_oncologist' => ['status' => HospitalCapabilityStatus::Available, 'detail' => '৮ জন নিবন্ধিত নারী অনকোলজিস্ট নিয়মিত দায়িত্ব পালন করেন', 'count' => 8],
            'blood_bank' => ['status' => HospitalCapabilityStatus::Available, 'detail' => '২৪ ঘণ্টা জরুরি ব্লাড ট্রান্সফিউশন ও সেপারেশন ইউনিট', 'count' => 1],
        ];

        foreach ($matrix as $key => $info) {
            if (! isset($capabilitiesByKey[$key])) {
                continue;
            }

            HospitalCapability::updateOrInsert(
                [
                    'hospital_id' => $hospital->id,
                    'capability_id' => $capabilitiesByKey[$key]->id,
                ],
                [
                    'status' => $info['status']->value,
                    'detail_bn' => $info['detail'],
                    'machine_count' => $info['count'],
                    'last_checked_at' => now(),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    private function seedWaitTimes(Hospital $hospital): void
    {
        $data = [
            ['service_key' => 'first_visit', 'min_weeks' => 0, 'max_weeks' => 1, 'label_bn' => 'প্রথম বহির্বিভাগে ডাক্তার দেখানো', 'severity' => HospitalWaitTimeSeverity::Short],
            ['service_key' => 'biopsy_report', 'min_weeks' => 1, 'max_weeks' => 2, 'label_bn' => 'বায়োপসি ও হিস্টোপ্যাথলজি রিপোর্ট', 'severity' => HospitalWaitTimeSeverity::Medium],
            ['service_key' => 'chemo_start', 'min_weeks' => 1, 'max_weeks' => 3, 'label_bn' => 'কেমোথেরাপি শুরু হওয়া', 'severity' => HospitalWaitTimeSeverity::Medium],
            ['service_key' => 'radiotherapy_start', 'min_weeks' => 4, 'max_weeks' => 8, 'label_bn' => 'রেডিওথেরাপির সিরিয়াল পাওয়া', 'severity' => HospitalWaitTimeSeverity::Long],
            ['service_key' => 'surgery_date', 'min_weeks' => 2, 'max_weeks' => 4, 'label_bn' => 'অপারেশনের তারিখ নির্ধারণ', 'severity' => HospitalWaitTimeSeverity::Medium],
        ];

        foreach ($data as $index => $row) {
            HospitalWaitTime::updateOrInsert(
                [
                    'hospital_id' => $hospital->id,
                    'service_key' => $row['service_key'],
                ],
                [
                    'min_weeks' => $row['min_weeks'],
                    'max_weeks' => $row['max_weeks'],
                    'label_bn' => $row['label_bn'],
                    'severity' => $row['severity']->value,
                    'sort_order' => $index + 1,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    private function seedCosts(Hospital $hospital): void
    {
        $data = [
            ['service_key' => 'outdoor_ticket', 'label_bn' => 'বহির্বিভাগ টিকিট ফি', 'min_amount' => 30, 'max_amount' => 50, 'note_bn' => 'প্রতি কার্যদিবসে সকাল ৮টা থেকে টিকিট পাওয়া যায়।'],
            ['service_key' => 'chemo_per_cycle', 'label_bn' => 'কেমোথেরাপি (প্রতি সাইকেল সরকারি চার্জ)', 'min_amount' => 500, 'max_amount' => 2000, 'note_bn' => 'ওষুধ রোগীর বাইরে থেকে কিনতে হতে পারে।'],
            ['service_key' => 'radiotherapy_full', 'label_bn' => 'রেডিওথেরাপি পূর্ণাঙ্গ কোর্স', 'min_amount' => 15000, 'max_amount' => 35000, 'note_bn' => 'সরকারি ভর্তুকি মূল্যে লিনিয়ার এক্সিলারেটর চিকিৎসা।'],
            ['service_key' => 'pet_ct_scan', 'label_bn' => 'পিইটি-সিটি স্ক্যান', 'min_amount' => 25000, 'max_amount' => 30000, 'note_bn' => 'বেসরকারি হাসপাতালের চেয়ে প্রায় অর্ধেক খরচ।'],
        ];

        foreach ($data as $index => $row) {
            HospitalCost::updateOrInsert(
                [
                    'hospital_id' => $hospital->id,
                    'service_key' => $row['service_key'],
                ],
                [
                    'label_bn' => $row['label_bn'],
                    'min_amount' => $row['min_amount'],
                    'max_amount' => $row['max_amount'],
                    'note_bn' => $row['note_bn'],
                    'sort_order' => $index + 1,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    private function seedPrepInfo(Hospital $hospital): void
    {
        $data = [
            [
                'key' => HospitalPrepKey::BloodBank,
                'title_bn' => 'ব্লাড ব্যাংক ও ডোনার প্রস্তুতি',
                'description_bn' => 'হাসপাতালে ব্লাড ব্যাংক থাকলেও যেকোনো সার্জারি বা কেমোর পূর্বে নির্দিষ্ট গ্রুপের ২ জন রক্তদাতা সাথে রাখা বাধ্যতামূলক।',
                'flag_text_bn' => 'ডোনার সাথে থাকা জরুরি',
                'flag_type' => HospitalFlagType::Warning,
            ],
            [
                'key' => HospitalPrepKey::MedicineSupply,
                'title_bn' => 'ওষুধের সহজলভ্যতা ও কেনাকাটা',
                'description_bn' => 'জরুরি প্রাথমিক ওষুধ সরকারিভাবে দেওয়া হয়, তবে বিশেষায়িত কেমো ও টার্গেটেড ওষুধ বাইরের অনুমোদিত ফার্মেসি থেকে আনতে হতে পারে।',
                'flag_text_bn' => 'কিছু ওষুধ বাইরে থেকে কিনতে হয়',
                'flag_type' => HospitalFlagType::Warning,
            ],
            [
                'key' => HospitalPrepKey::AttendantPolicy,
                'title_bn' => 'রোগীর সঙ্গে থাকার নিয়ম',
                'description_bn' => 'ওয়ার্ডে রোগীর সাথে মাত্র ১ জন অ্যাটেনডেন্ট পাসের মাধ্যমে সার্বক্ষণিক থাকতে পারবেন। দর্শনার্থীদের নির্দিষ্ট সময়ে প্রবেশযোগ্য।',
                'flag_text_bn' => '১ জন অ্যাটেনডেন্ট অনুমোদিত',
                'flag_type' => HospitalFlagType::Positive,
            ],
            [
                'key' => HospitalPrepKey::RecordsReturn,
                'title_bn' => 'রিপোর্ট ও ফাইল সংরক্ষণ',
                'description_bn' => 'হাসপাতাল নিজস্ব ফাইল সংরক্ষণ করে, তবে রোগীর সব মূল বায়োপসি স্লাইড ও পূর্ববর্তী টেস্টের কপি সাথে রাখা জরুরি।',
                'flag_text_bn' => 'মূল কপি নিজের কাছে রাখুন',
                'flag_type' => HospitalFlagType::Positive,
            ],
        ];

        foreach ($data as $index => $row) {
            HospitalPrepInfo::updateOrInsert(
                [
                    'hospital_id' => $hospital->id,
                    'key' => $row['key']->value,
                ],
                [
                    'title_bn' => $row['title_bn'],
                    'description_bn' => $row['description_bn'],
                    'flag_text_bn' => $row['flag_text_bn'],
                    'flag_type' => $row['flag_type']->value,
                    'sort_order' => $index + 1,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    private function seedPracticalInfo(Hospital $hospital): void
    {
        $data = [
            [
                'key' => HospitalPracticalKey::Documents,
                'title_bn' => 'প্রথম দিনে যা যা সাথে আনবেন',
                'description_bn' => 'রোগীর জাতীয় পরিচয়পত্র (NID/জন্মনিবন্ধন), পূর্বের সব প্রেসক্রিপশন, বায়োপসি রিপোর্ট ও স্লাইড, সিটি স্ক্যান বা এমআরআই ফিল্ম।',
                'icon' => 'file-text',
            ],
            [
                'key' => HospitalPracticalKey::Timing,
                'title_bn' => 'টিকিট ও কাউন্টার খোলার সময়',
                'description_bn' => 'বহির্বিভাগের টিকিট সকাল ৮টা থেকে বেলা ১২টা পর্যন্ত দেওয়া হয়। সকাল সাড়ে ৮টা থেকে দুপুর আড়াইটা পর্যন্ত ডাক্তার দেখা হয়।',
                'icon' => 'clock',
            ],
            [
                'key' => HospitalPracticalKey::Accommodation,
                'title_bn' => 'ঢাকার বাইরের রোগীদের থাকার ব্যবস্থা',
                'description_bn' => 'মহাখালী ও আশপাশের এলাকায় স্বল্প খরচে হোটেল ও সেবা সংস্থার রেস্ট হাউজ রয়েছে যেখানে দীর্ঘমেয়াদি চিকিৎসাধীন রোগীরা থাকতে পারেন।',
                'icon' => 'home',
            ],
            [
                'key' => HospitalPracticalKey::FinancialAid,
                'title_bn' => 'হাসপাতাল সমাজসেবা কার্যালয় সহায়তা',
                'description_bn' => 'হাসপাতালের ভেতর সমাজসেবা অধিদপ্তরের কার্যালয় রয়েছে। অসচ্ছলতার সনদ জমা দিলে ওষুধের জন্য সরকারি সহায়তা পাওয়া যায়।',
                'icon' => 'heart-handshake',
            ],
        ];

        foreach ($data as $index => $row) {
            HospitalPracticalInfo::updateOrInsert(
                [
                    'hospital_id' => $hospital->id,
                    'key' => $row['key']->value,
                ],
                [
                    'title_bn' => $row['title_bn'],
                    'description_bn' => $row['description_bn'],
                    'icon' => $row['icon'],
                    'sort_order' => $index + 1,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    private function seedVideos(Hospital $hospital): void
    {
        HospitalVideo::updateOrInsert(
            [
                'hospital_id' => $hospital->id,
                'video_url' => 'https://www.youtube.com/watch?v=demo_nicrh_guide',
            ],
            [
                'platform' => 'youtube',
                'title_bn' => 'জাতীয় ক্যান্সার হাসপাতালে প্রথম দিন: টিকিট কাটা থেকে ডাক্তার দেখানো',
                'description_bn' => 'ঢাকার বাইরে থেকে আসা পরিবারের জন্য ধাপে ধাপে দিকনির্দেশনা — কোথায় টিকিট কাটবেন, কোন তলায় কোন বিভাগ, কীভাবে পরীক্ষা করাবেন।',
                'duration_seconds' => 180,
                'produced_by' => 'CancerCare Bangladesh',
                'sort_order' => 1,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    private function linkDoctors(Hospital $hospital): void
    {
        $doctors = Doctor::take(3)->get();
        foreach ($doctors as $index => $doc) {
            DB::table('hospital_doctor')->updateOrInsert(
                [
                    'hospital_id' => $hospital->id,
                    'doctor_id' => $doc->id,
                ],
                [
                    'schedule_note_bn' => 'রবি, মঙ্গল ও বৃহস্পতিবার সকাল ৯টা - দুপুর ১টা (ইউনিট-২)',
                    'sort_order' => $index + 1,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
