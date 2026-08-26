<?php

namespace Database\Seeders;

use App\Enums\DoctorStatus;
use App\Enums\GuideStatus;
use App\Models\CancerType;
use App\Models\Doctor;
use App\Models\Guide;
use App\Models\GuideFaq;
use App\Models\GuideMyth;
use App\Models\GuideStage;
use App\Models\GuideStep;
use App\Models\GuideTerm;
use App\Models\GuideVideo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * গাইড ডোমেইনের (Phase 3) সব টেবিলে সমৃদ্ধ ডেমো/SEO ডেটা।
 *
 * চালাতে: php artisan db:seed --class=GuideDemoSeeder
 */
class GuideDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure doctors exist for reviews
        $doctorSadia = Doctor::where('name_bn', 'like', '%সাদিয়া রহমান%')->first()
            ?? $this->createFallbackDoctor('ডা. সাদিয়া রহমান', 'Dr. Sadia Rahman', 'female', 'সার্জিক্যাল অনকোলজিস্ট');

        $doctorMohaimin = Doctor::where('name_bn', 'like', '%মোহাইমিনুল%')->first()
            ?? $this->createFallbackDoctor('ডা. মোহাইমিনুল ইসলাম', 'Dr. Mohaiminul Islam', 'male', 'মেডিকেল অনকোলজিস্ট');

        $doctorFarhana = Doctor::where('name_bn', 'like', '%ফারহানা%')->first()
            ?? $this->createFallbackDoctor('ডা. ফারহানা হক', 'Dr. Farhana Haque', 'female', 'রেডিয়েশন অনকোলজিস্ট');

        $doctorTariq = Doctor::where('name_bn', 'like', '%তারেক%')->first()
            ?? $this->createFallbackDoctor('ডা. তারেক মনসুর', 'Dr. Tariq Mansur', 'male', 'হেমাটোলজিস্ট ও অনকোলজিস্ট');

        $doctorNusrat = Doctor::where('name_bn', 'like', '%নুসরাত%')->first()
            ?? $this->createFallbackDoctor('ডা. নুসরাত জাহান', 'Dr. Nusrat Jahan', 'female', 'হেড-নেক অনকোসার্জন');

        // 2. Guide 1: স্তন ক্যান্সার (Breast Cancer)
        $this->seedBreastCancerGuide($doctorSadia);

        // 3. Guide 2: ফুসফুস ক্যান্সার (Lung Cancer)
        $this->seedLungCancerGuide($doctorMohaimin);

        // 4. Guide 3: জরায়ু মুখ ক্যান্সার (Cervical Cancer)
        $this->seedCervicalCancerGuide($doctorFarhana);

        // 5. Guide 4: রক্তের ক্যান্সার (Blood Cancer)
        $this->seedBloodCancerGuide($doctorTariq);

        // 6. Guide 5: মুখের ক্যান্সার (Oral Cancer)
        $this->seedOralCancerGuide($doctorNusrat);
    }

    private function createFallbackDoctor(string $nameBn, string $nameEn, string $gender, string $position): Doctor
    {
        return Doctor::create([
            'name_bn' => $nameBn,
            'name_en' => $nameEn,
            'slug' => \Illuminate\Support\Str::slug($nameEn) . '-' . uniqid(),
            'bmdc_number' => 'BMDC-' . rand(10000, 99999),
            'bmdc_verified_at' => now()->subMonths(6),
            'photo_path' => 'doctors/default.jpg',
            'degrees_line_bn' => 'এমবিবিএস, এফসিপিএস, এমএস',
            'experience_years' => 12,
            'current_position_bn' => $position,
            'gender' => $gender,
            'status' => DoctorStatus::Published,
            'doctor_approved_at' => now()->subMonths(6),
        ]);
    }

    private function seedBreastCancerGuide(Doctor $doctor): void
    {
        $cancerType = CancerType::where('slug', 'breast-cancer')->first();
        if (!$cancerType) return;

        $cancerType->update(['guide_published' => true]);

        $guide = Guide::updateOrCreate(
            ['cancer_type_id' => $cancerType->id],
            [
                'title_bn' => 'স্তন ক্যান্সার — লক্ষণ, রিপোর্ট ডিকোডার, স্টেজ ও চিকিৎসা গাইড',
                'intro_bn' => 'স্তন ক্যান্সার বাংলাদেশে নারীদের মধ্যে সবচেয়ে সাধারণ ক্যান্সার। সময়মতো নির্ণয় হলে ৯০% এরও বেশি রোগী সম্পূর্ণ নিরাময় লাভ করেন। বায়োপসি রিপোর্ট বোঝা, স্টেজ ও চিকিৎসার সব তথ্য সহজ বাংলায় জানুন।',
                'meta_title' => 'স্তন ক্যান্সার চিকিৎসা গাইড — লক্ষণ, বায়োপসি রিপোর্ট ও খরচ | CancerCare Bangladesh',
                'meta_description' => 'স্তন ক্যান্সারের লক্ষণ, বায়োপসি ও IHC রিপোর্ট ডিকোডার (HER2, ER, PR, Grade), স্টেজের চিকিৎসা এবং খরচের পূর্ণাঙ্গ গাইড। বিশ্ব স্বাস্থ্য সংস্থা ও বিশেষজ্ঞ অনকোলজিস্টদের দ্বারা পর্যালোচিত।',
                'reviewed_by_doctor_id' => $doctor->id,
                'reviewed_at' => now()->subDays(10),
                'sources_note_bn' => 'WHO, NCCN ও ESMO গাইডলাইন ২০২৬',
                'read_minutes' => 6,
                'status' => GuideStatus::Published,
                'published_at' => now()->subMonths(2),
                'last_updated_at' => now()->subDays(5),
            ]
        );

        // Videos
        GuideVideo::updateOrCreate(
            ['guide_id' => $guide->id, 'title_bn' => 'স্তন ক্যান্সার — যা সবার আগে জানা দরকার'],
            [
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'platform' => 'youtube',
                'description_bn' => 'লক্ষণ কী, কখন ডাক্তার দেখাবেন, আর কেন দেরি করলে চিকিৎসা জটিল হয়ে যায় — সহজ বাংলায় ২ মিনিটে শুনুন।',
                'duration_seconds' => 138,
                'doctor_id' => $doctor->id,
                'sort_order' => 1,
            ]
        );

        // Terms
        $terms = [
            [
                'code' => 'Invasive Ductal Carcinoma (IDC)',
                'slug' => 'invasive-ductal-carcinoma',
                'hint_bn' => 'সবচেয়ে সাধারণ ধরন',
                'plain_explanation_bn' => "স্তনের দুধ বহনকারী নালি থেকে শুরু হওয়া ক্যান্সার, যা আশপাশের টিস্যুতে পৌঁছেছে। স্তন ক্যান্সারের প্রায় ৮০% এই ধরনের — অর্থাৎ এটি চিকিৎসকদের কাছে সবচেয়ে পরিচিত ও সর্বোচ্চ নিরাময়যোগ্য ধরন।",
                'why_matters_bn' => '"Invasive" শুনে আতঙ্কিত হবেন না — এর মানে নালির বাইরে গেছে, পুরো শরীরে ছড়িয়েছে এমন নয়। কতদূর গেছে তা স্টেজ দিয়ে নির্ধারিত হয়।',
                'scale' => null,
                'search_keywords' => 'IDC invasive ductal carcinoma স্তন ডাক্টাল কার্সিনোমা',
                'sort_order' => 1,
            ],
            [
                'code' => 'Grade 1 / 2 / 3',
                'slug' => 'histological-grade',
                'hint_bn' => 'কোষ কত দ্রুত বিভাজিত হচ্ছে',
                'plain_explanation_bn' => "গ্রেড বলে দেয় ক্যান্সার কোষগুলো দেখতে সাধারণ কোষের চেয়ে কতটা ভিন্ন এবং কত দ্রুত বৃদ্ধি পাচ্ছে। মনে রাখবেন — গ্রেড এবং স্টেজ এক জিনিস নয়।",
                'why_matters_bn' => 'উঁচু গ্রেড মানেই নিরাময় অসম্ভব নয় — বরং দ্রুত বর্ধনশীল কোষ কেমোথেরাপিতে বেশি দ্রুত সাড়া দেয়। ডাক্তার এটি দেখে কেমোথেরাপির প্রয়োজনীয়তা নির্ধারণ করেন।',
                'scale' => [
                    ['label' => 'গ্রেড ১', 'desc' => 'ধীরগতির কোষ'],
                    ['label' => 'গ্রেড ২', 'desc' => 'মাঝারি গতির কোষ'],
                    ['label' => 'গ্রেড ৩', 'desc' => 'দ্রুতগতির কোষ'],
                ],
                'search_keywords' => 'Grade histological tumor grade গ্রেড ১ ২ ৩',
                'sort_order' => 2,
            ],
            [
                'code' => 'ER / PR Positive বা Negative',
                'slug' => 'er-pr-receptor',
                'hint_bn' => 'হরমোন থেরাপি কাজ করবে কিনা',
                'plain_explanation_bn' => "ক্যান্সার কোষ শরীরের স্বাভাবিক হরমোন (ইস্ট্রোজেন ও প্রোজেস্টেরন) গ্রহণ করে বৃদ্ধি পাচ্ছে কিনা তা জানায়। Positive হওয়া অত্যন্ত ভালো খবর — কারণ হরমোন ব্লকার ওষুধ দিয়ে ক্যান্সারের বৃদ্ধির রসদ বন্ধ করে দেওয়া যায়।",
                'why_matters_bn' => 'ER/PR Positive রোগীরা অপারেশনের পর দৈনিক একটি করে হরমোন ট্যাবলেট (যেমন ট্যামক্সিফেন) খান, যা রোগ ফিরে আসার ঝুঁকি প্রায় ৫০% কমিয়ে দেয়।',
                'scale' => null,
                'search_keywords' => 'ER PR estrogen progesterone receptor হরমোন রিসেপ্টর পজিটিভ নেগেটিভ',
                'sort_order' => 3,
            ],
            [
                'code' => 'HER2 Positive বা Negative',
                'slug' => 'her2-neu-status',
                'hint_bn' => 'টার্গেটেড থেরাপির সুযোগ',
                'plain_explanation_bn' => "HER2 একটি বিশেষ প্রোটিন যা কোষে অতিরিক্ত থাকলে কোষ দ্রুত বাড়ে। এটি Positive হলে ট্রাস্টুজুমাব (Trastuzumab) জাতীয় আধুনিক টার্গেটেড থেরাপি দেওয়া হয়, যা অত্যন্ত কার্যকর।",
                'why_matters_bn' => 'HER2 পজিটিভ চিকিৎসার জন্য আর্থিক প্রস্তুতি আগে থেকেই রাখা প্রয়োজন, কারণ টার্গেটেড থেরাপির পূর্ণ কোর্সে বাজেট পরিকল্পনা জরুরি।',
                'scale' => null,
                'search_keywords' => 'HER2 HER2/neu positive negative ট্রাস্টুজুমাব টার্গেটেড থেরাপি',
                'sort_order' => 4,
            ],
            [
                'code' => 'Triple Negative (TNBC)',
                'slug' => 'triple-negative-breast-cancer',
                'hint_bn' => 'ER, PR ও HER2 তিনটিই নেগেটিভ',
                'plain_explanation_bn' => "যখন কোষে ER, PR এবং HER2 কোনো রিসেপ্টরই অতিরিক্ত থাকে না। এতে হরমোন থেরাপি প্রযোজ্য নয়, তবে কেমোথেরাপি সবচেয়ে প্রধান ও কার্যকর চিকিৎসা।",
                'why_matters_bn' => 'TNBC কেমোথেরাপিতে খুব ভালো সাড়া দেয়। অভিজ্ঞ মেডিকেল অনকোলজিস্টের নিবিড় তত্ত্বাবধানে চিকিৎসা নিলে পূর্ণ সুস্থতা সম্ভব।',
                'scale' => null,
                'search_keywords' => 'TNBC triple negative ট্রিপল নেগেটিভ',
                'sort_order' => 5,
            ],
            [
                'code' => 'Ki-67 Index',
                'slug' => 'ki-67-proliferation-index',
                'hint_bn' => 'কোষ বিভাজনের শতকরা হার',
                'plain_explanation_bn' => "টিউমারের কত শতাংশ কোষ সক্রিয়ভাবে বিভাজিত হচ্ছে তা শতকরা হিসেবে প্রকাশ করে। ২০%-এর কম হলে ধীরগতির এবং এর বেশি হলে দ্রুতগতির বিবেচনা করা হয়।",
                'why_matters_bn' => 'শুধু Ki-67 দেখে আতঙ্কিত হবেন না — এটি অন্যান্য রিসেপ্টরের সাথে সমন্বয় করে কেমোর সময়কাল ঠিক করতে কাজে লাগে।',
                'scale' => null,
                'search_keywords' => 'Ki67 Ki-67 proliferation index সেল ডিভিশন',
                'sort_order' => 6,
            ],
            [
                'code' => 'Lymph Node Involvement',
                'slug' => 'lymph-node-involvement',
                'hint_bn' => 'বগলের লসিকা গ্রন্থিতে বিস্তার',
                'plain_explanation_bn' => 'বগলের লিম্ফ নোডে ক্যান্সার পৌঁছেছে কিনা। রিপোর্টে সাধারণত "0/12" বা "2/15" আকারে লেখা থাকে — যার অর্থ মোট পরীক্ষিত নোডের মধ্যে কতটিতে উপস্থিতি ছিল।',
                'why_matters_bn' => 'নোড মুক্ত থাকলে স্টেজ কম হয় এবং রেডিওথেরাপি ও ওষুধের কোর্স নির্ধারণ সহজ হয়।',
                'scale' => null,
                'search_keywords' => 'Lymph node axillary নোড লিম্ফ নোড লসিকা গ্রন্থি',
                'sort_order' => 7,
            ],
        ];

        foreach ($terms as $t) {
            GuideTerm::updateOrCreate(['guide_id' => $guide->id, 'slug' => $t['slug']], $t);
        }

        // Stages
        $stages = [
            [
                'stage' => 'স্টেজ ১',
                'title_bn' => 'টিউমার ২ সেমি পর্যন্ত, লসিকা গ্রন্থিতে ছড়ায়নি',
                'description_bn' => 'সবচেয়ে আশাব্যঞ্জক পর্যায়। বেশিরভাগ ক্ষেত্রে স্তন রক্ষা করে শুধু টিউমার অপসারণ (BCS) সম্ভব হয়, এরপর সহনীয় রেডিওথেরাপি। ৯৫% এর বেশি রোগী স্বাভাবিক জীবনযাপন করেন।',
                'typical_treatment_bn' => 'ব্রেস্ট কনজারভিং সার্জারি + রেডিওথেরাপি (প্রয়োজনে হরমোন থেরাপি)',
                'duration_bn' => '৩–৫ মাস',
                'cost_min' => 120000,
                'cost_max' => 220000,
                'severity_color' => 'teal',
                'sort_order' => 1,
            ],
            [
                'stage' => 'স্টেজ ২',
                'title_bn' => 'টিউমার ২-৫ সেমি অথবা কয়েকটি লসিকা গ্রন্থিতে উপস্থিতি',
                'description_bn' => 'বাংলাদেশে সবচেয়ে বেশি রোগী এই পর্যায়ে চিকিৎসা শুরু করেন। অপারেশনের আগে বা পরে কেমোথেরাপি দিয়ে টিউমার নিয়ন্ত্রণ করা হয়। নিরাময়ের হার অত্যন্ত ইতিবাচক।',
                'typical_treatment_bn' => 'কেমোথেরাপি + সার্জারি + রেডিওথেরাপি + হরমোন থেরাপি',
                'duration_bn' => '৬–৮ মাস',
                'cost_min' => 180000,
                'cost_max' => 350000,
                'severity_color' => 'teal',
                'sort_order' => 2,
            ],
            [
                'stage' => 'স্টেজ ৩',
                'title_bn' => 'টিউমার ৫ সেমির বেশি অথবা একাধিক গ্রন্থিতে বিস্তৃত',
                'description_bn' => 'যুগপৎ চিকিৎসা প্রয়োজন। কেমোথেরাপির মাধ্যমে টিউমার সংকুচিত করে সফল সার্জারি ও রেডিয়েশন দেওয়া হয়। যথাযথ প্রটোকল মানলে দীর্ঘমেয়াদী রোগমুক্তি সম্ভব।',
                'typical_treatment_bn' => 'নব্য কেমোথেরাপি (Neoadjuvant) + মডিফাইড রেডিক্যাল মাস্টেক্টমি + রেডিয়েশন',
                'duration_bn' => '৮–১২ মাস',
                'cost_min' => 250000,
                'cost_max' => 480000,
                'severity_color' => 'amber',
                'sort_order' => 3,
            ],
            [
                'stage' => 'স্টেজ ৪',
                'title_bn' => 'ক্যান্সার হাড়, ফুসফুস, লিভার বা অন্যান্য অঙ্গে বিস্তৃত',
                'description_bn' => 'চিকিৎসার মূল উদ্দেশ্য ক্যান্সার নিয়ন্ত্রণে রাখা, জীবনের মান উন্নত রাখা ও ব্যথা মুক্ত রাখা। আধুনিক টার্গেটেড ও ইমিউনোথেরাপির কল্যাণে রোগীরা বছরের পর বছর সুস্থ থাকেন।',
                'typical_treatment_bn' => 'টার্গেটেড থেরাপি / কেমোথেরাপি + হরমোন থেরাপি + উপশমমূলক কেয়ার',
                'duration_bn' => 'চলমান পর্যবেক্ষণ ও চিকিৎসা',
                'cost_min' => 40000,
                'cost_max' => 90000,
                'severity_color' => 'rose',
                'sort_order' => 4,
            ],
        ];

        foreach ($stages as $s) {
            GuideStage::updateOrCreate(['guide_id' => $guide->id, 'stage' => $s['stage']], $s);
        }

        // Steps
        $steps = [
            [
                'step_no' => 1,
                'title_bn' => 'অনকোলজিস্টের সাথে পরামর্শ ও মূল্যায়ন',
                'description_bn' => 'সাধারণ চিকিৎসকের বদলে সরাসরি একজন সার্জিক্যাল বা মেডিকেল অনকোলজিস্টের কাছে যান।',
                'when_label_bn' => 'যত দ্রুত সম্ভব',
                'urgency' => 'urgent',
                'items' => [
                    'বায়োপসি রিপোর্ট ও IHC রিপোর্ট ফাইল আকারে গুছিয়ে সাথে নিন',
                    'ম্যামোগ্রাফি বা আল্ট্রাসনোগ্রামের আসল ফিল্ম সাথে রাখুন',
                    'প্রশ্ন ও সন্দেহের তালিকা ডায়েরিতে লিখে নিয়ে যান',
                ],
                'sort_order' => 1,
            ],
            [
                'step_no' => 2,
                'title_bn' => 'স্টেজ নির্ধারণের পরীক্ষা সম্পন্ন করা',
                'description_bn' => 'চিকিৎসার রোডম্যাপ তৈরির জন্য বুকের সিটি স্ক্যান বা পেটের আল্ট্রাসাউন্ড প্রয়োজন হয়।',
                'when_label_bn' => '১–২ সপ্তাহ',
                'urgency' => 'normal',
                'items' => [
                    'সরকারি ক্যান্সার হাসপাতালে (যেমন NICRH) পরীক্ষা করালে খরচ সাশ্রয়ী হয়',
                    'সকল টেস্টের ডিজিটাল কপি ও প্রিন্ট সংরক্ষণ করুন',
                ],
                'sort_order' => 2,
            ],
            [
                'step_no' => 3,
                'title_bn' => 'চিকিৎসা পরিকল্পনা ও দ্বিতীয় মতামত',
                'description_bn' => 'অপারেশন আগে হবে নাকি কেমোথেরাপি, কয়টি সাইকেল লাগবে এবং আনুমানিক বাজেট চিকিৎসকের সাথে খোলামেলা আলোচনা করুন।',
                'when_label_bn' => 'চিকিৎসা শুরুর আগে',
                'urgency' => 'normal',
                'items' => [
                    'অন্য একজন বিশেষজ্ঞের দ্বিতীয় মতামত (Second Opinion) নেওয়া ইতিবাচক',
                    'সরকারি ও বেসরকারি খরচের বাস্তব তুলনা করে পরিকল্পনা স্থির করুন',
                ],
                'sort_order' => 3,
            ],
            [
                'step_no' => 4,
                'title_bn' => 'পারিবারিক ও মানসিক সমর্থন প্রস্তুতি',
                'description_bn' => 'ক্যান্সার দীর্ঘমেয়াদী যাত্রা। পরিবারের সদস্যদের মাঝে দায়িত্ব বণ্টন এবং আর্থিক প্রস্তুতি নিন।',
                'when_label_bn' => 'সমান্তরালে',
                'urgency' => 'normal',
                'items' => [
                    'সমাজসেবা অধিদপ্তর বা দাতব্য তহবিলের আর্থিক সহায়তার সুযোগ যাচাই করুন',
                    'রোগীর মনোবল বৃদ্ধি ও পুষ্টিকর খাবারের ব্যবস্থা রাখুন',
                ],
                'sort_order' => 4,
            ],
        ];

        foreach ($steps as $st) {
            GuideStep::updateOrCreate(['guide_id' => $guide->id, 'step_no' => $st['step_no']], $st);
        }

        // Myths
        $myths = [
            [
                'myth_bn' => 'ক্যান্সার মানেই মৃত্যু, চিকিৎসা করে কোনো লাভ হয় না।',
                'truth_bn' => 'স্তন ক্যান্সার প্রাথমিক ধাপে শনাক্ত হলে ৯০%-এর বেশি রোগী সম্পূর্ণ সুস্থ হয়ে স্বাভাবিক জীবনযাপন করেন। দেরি না করে সঠিক চিকিৎসা শুরু করাই সবচেয়ে জরুরি।',
                'sort_order' => 1,
            ],
            [
                'myth_bn' => 'বায়োপসি বা অপারেশন করলে ক্যান্সার শরীরে ছড়িয়ে পড়ে।',
                'truth_bn' => 'এটি সম্পূর্ণ ভুল ধারণা। বায়োপসি ছাড়া সঠিক ওষুধ নির্ধারণ অসম্ভব। বাতাস লাগলে বা অপারেশনে ক্যান্সার ছড়ায় না, বরং অপারেশনই ক্যান্সার নির্মূলের প্রধান হাতিয়ার।',
                'sort_order' => 2,
            ],
            [
                'myth_bn' => 'ভেষজ, হোমিওপ্যাথি বা কবিরাজি চিকিৎসায় ক্যান্সার সম্পূর্ণ নিরাময় হয়।',
                'truth_bn' => 'এসব বিকল্প পদ্ধতিতে ক্যান্সার নিরাময়ের কোনো বৈজ্ঞানিক ভিত্তি নেই। এর পেছনে সময় নষ্ট করলে ক্যান্সার নিঃশব্দে পরবর্তী ধাপে চলে যায়।',
                'sort_order' => 3,
            ],
            [
                'myth_bn' => 'কেমোথেরাপির বিষাক্ততায় মানুষ দ্রুত মারা যায়।',
                'truth_bn' => 'কেমোথেরাপির আধুনিক ওষুধে পার্শ্বপ্রতিক্রিয়া এখন অনেকটাই নিয়ন্ত্রণযোগ্য। এটি ক্যান্সার কোষ ধ্বংস করে আয়ু ও নিরাময়ের হার বহুগুণ বাড়িয়ে দেয়।',
                'sort_order' => 4,
            ],
        ];

        foreach ($myths as $m) {
            GuideMyth::updateOrCreate(['guide_id' => $guide->id, 'myth_bn' => $m['myth_bn']], $m);
        }

        // FAQs
        $faqs = [
            [
                'question_bn' => 'কেমোথেরাপি নিলে কি সব চুল পড়ে যাবে?',
                'answer_bn' => 'সব কেমোথেরাপির ওষুধে চুল পড়ে না। যেসব ওষুধে চুল পড়ে, সেগুলোতেও চিকিৎসা শেষ হওয়ার ২-৩ মাসের মধ্যে আবার নতুন ও স্বাস্থ্যোজ্জ্বল চুল গজায়। এটি সম্পূর্ণ অস্থায়ী।',
                'sort_order' => 1,
            ],
            [
                'question_bn' => 'পুরো স্তন কি কেটে ফেলে দিতে হয়?',
                'answer_bn' => 'না, টিউমার ছোট থাকলে এবং আগে শনাক্ত হলে শুধুমাত্র টিউমার ও আশপাশের অল্প টিস্যু কেটে স্তন অক্ষত রাখা যায় (Breast Conserving Surgery)।',
                'sort_order' => 2,
            ],
            [
                'question_bn' => 'চিকিৎসা চলাকালীন কি স্বাভাবিক কাজ বা চাকরি করা সম্ভব?',
                'answer_bn' => 'হ্যাঁ, অনেকেই চিকিৎসাধীন অবস্থায় স্বাভাবিক পেশাগত দায়িত্ব পালন করতে পারেন। কেমোর দিনের পর দুই-একদিন বিশ্রাম নিয়ে বাকি দিনগুলোতে স্বাভাবিকভাবে চলাফেরা করা যায়।',
                'sort_order' => 3,
            ],
            [
                'question_bn' => 'সরকারি হাসপাতালে চিকিৎসা নিলে কি মানসম্মত সেবা পাওয়া যায়?',
                'answer_bn' => 'জাতীয় ক্যান্সার গবেষণা ইনস্টিটিউট (NICRH) ও ঢাকা মেডিকেলসহ সরকারি হাসপাতালে আন্তর্জাতিক প্রটোকল অনুসারে চিকিৎসা দেওয়া হয়। অনেক নামকরা বিশেষজ্ঞ সেখানে কর্মরত। খরচ ৬০–৮০% পর্যন্ত সাশ্রয়ী।',
                'sort_order' => 4,
            ],
        ];

        foreach ($faqs as $f) {
            GuideFaq::updateOrCreate(['guide_id' => $guide->id, 'question_bn' => $f['question_bn']], $f);
        }
    }

    private function seedLungCancerGuide(Doctor $doctor): void
    {
        $cancerType = CancerType::where('slug', 'lung-cancer')->first();
        if (!$cancerType) return;

        $cancerType->update(['guide_published' => true]);

        $guide = Guide::updateOrCreate(
            ['cancer_type_id' => $cancerType->id],
            [
                'title_bn' => 'ফুসফুস ক্যান্সার — লক্ষণ, জেনেটিক টেস্ট, স্টেজ ও চিকিৎসা গাইড',
                'intro_bn' => 'ধূমপান ও বায়ুদূষণের কারণে বাংলাদেশে ফুসফুসের ক্যান্সার দ্রুত বাড়ছে। আধুনিক টার্গেটেড থেরাপি ও ইমিউনোথেরাপির সাহায্যে ফুসফুস ক্যান্সারে রোগীরা দীর্ঘমেয়াদে সুস্থ থাকছেন।',
                'meta_title' => 'ফুসফুস ক্যান্সার চিকিৎসা ও রিপোর্ট নির্দেশিকা | CancerCare Bangladesh',
                'meta_description' => 'ফুসফুস ক্যান্সারের প্রাথমিক লক্ষণ, সিটি স্ক্যান, ব্রঙ্কোস্কোপি, EGFR/ALK জেনেটিক টেস্ট ও আধুনিক চিকিৎসার সম্পূর্ণ বাংলা গাইড।',
                'reviewed_by_doctor_id' => $doctor->id,
                'reviewed_at' => now()->subDays(15),
                'sources_note_bn' => 'WHO & NCCN Lung Cancer Guidelines 2026',
                'read_minutes' => 7,
                'status' => GuideStatus::Published,
                'published_at' => now()->subMonths(1),
                'last_updated_at' => now()->subDays(3),
            ]
        );

        $terms = [
            [
                'code' => 'Non-Small Cell Lung Cancer (NSCLC)',
                'slug' => 'nsclc',
                'hint_bn' => 'ফুসফুস ক্যান্সারের প্রধান ধরন',
                'plain_explanation_bn' => 'ফুসফুসের ক্যান্সারের প্রায় ৮৫% হলো NSCLC। এটি তুলনামূলকভাবে ধীরে বাড়ে এবং প্রাথমিক ধাপে সার্জারি বা টার্গেটেড থেরাপিতে চমৎকার নিরাময় সম্ভব।',
                'why_matters_bn' => 'ধরণ শনাক্ত হলে পরবর্তী জেনেটিক মিউটেশন পরীক্ষা (EGFR, ALK) করে ট্যাবলেট জাতীয় ওষুধ দেওয়া যায়।',
                'scale' => null,
                'search_keywords' => 'NSCLC Non-small cell lung cancer এনএসসিএলসি',
                'sort_order' => 1,
            ],
            [
                'code' => 'EGFR Mutation Status',
                'slug' => 'egfr-mutation',
                'hint_bn' => 'টার্গেটেড ট্যাবলেটের সুযোগ',
                'plain_explanation_bn' => 'এশিয়ার অধূমপায়ী ফুসফুস ক্যান্সার রোগীদের প্রায় ৪০-৫০% ক্ষেত্রে EGFR মিউটেশন পজিটিভ হয়। এটি পজিটিভ হলে সাধারণ কেমো না দিয়ে দৈনিক একটি ট্যাবলেট (TKI) খেয়ে ক্যান্সার নিয়ন্ত্রণে রাখা যায়।',
                'why_matters_bn' => 'কেমোর কষ্ট ছাড়াই বাসায় বসে ওসিমেরটিনিব বা জেফিটিনিব ট্যাবলেট খাওয়ার সুযোগ তৈরি হয়।',
                'scale' => null,
                'search_keywords' => 'EGFR Osimertinib Gefitinib ইজিএফআর মিউটেশন',
                'sort_order' => 2,
            ],
            [
                'code' => 'PD-L1 Expression',
                'slug' => 'pd-l1-expression',
                'hint_bn' => 'ইমিউনোথেরাপি কাজ করবে কিনা',
                'plain_explanation_bn' => 'রোগীর রোগ প্রতিরোধ ক্ষমতা (Immune system) ক্যান্সার কোষকে শনাক্ত করে ধ্বংস করতে পারবে কিনা তা নির্ধারণ করে। PD-L1 ৫০% এর বেশি হলে ইমিউনোথেরাপি অত্যন্ত সফল হয়।',
                'why_matters_bn' => 'পেমব্রোলিজুমাব (Pembrolizumab) জাতীয় ইমিউনোথেরাপি চিকিৎসার পথ খুলে দেয়।',
                'scale' => null,
                'search_keywords' => 'PD-L1 immunotherapy ইমিউনোথেরাপি',
                'sort_order' => 3,
            ],
        ];

        foreach ($terms as $t) {
            GuideTerm::updateOrCreate(['guide_id' => $guide->id, 'slug' => $t['slug']], $t);
        }

        $stages = [
            [
                'stage' => 'স্টেজ ১',
                'title_bn' => 'টিউমার ফুসফুসের নির্দিষ্ট অংশে সীমাবদ্ধ',
                'description_bn' => 'টিউমার সম্পূর্ণ অস্ত্রোপচারের মাধ্যমে অপসারণ করে নিরাময় সম্ভব।',
                'typical_treatment_bn' => 'লোবেক্টমি (অস্ত্রোপচার) + পর্যবেক্ষণ',
                'duration_bn' => '২–৩ মাস',
                'cost_min' => 150000,
                'cost_max' => 280000,
                'severity_color' => 'teal',
                'sort_order' => 1,
            ],
            [
                'stage' => 'স্টেজ ২–৩',
                'title_bn' => 'নিকটবর্তী লসিকা গ্রন্থিতে বা বুকে বিস্তৃত',
                'description_bn' => 'কেমোথেরাপি এবং রেডিওথেরাপির সমন্বয়ে টিউমার নিষ্ক্রিয় করা হয়।',
                'typical_treatment_bn' => 'কেমো-রেডিওথেরাপি + ইমিউনোথেরাপি',
                'duration_bn' => '৬–৯ মাস',
                'cost_min' => 220000,
                'cost_max' => 450000,
                'severity_color' => 'amber',
                'sort_order' => 2,
            ],
            [
                'stage' => 'স্টেজ ৪',
                'title_bn' => 'অন্য ফুসফুস, মস্তিষ্ক, লিভার বা হাড়ে বিস্তৃত',
                'description_bn' => 'জেনেটিক মিউটেশন পরীক্ষা করে টার্গেটেড থেরাপির ট্যাবলেট বা ইমিউনোথেরাপির মাধ্যমে দীর্ঘ বছর নিয়ন্ত্রণে রাখা যায়।',
                'typical_treatment_bn' => 'টার্গেটেড থেরাপি (ট্যাবলেট) / ইমিউনোথেরাপি',
                'duration_bn' => 'চলমান পর্যবেক্ষণ',
                'cost_min' => 50000,
                'cost_max' => 120000,
                'severity_color' => 'rose',
                'sort_order' => 3,
            ],
        ];

        foreach ($stages as $s) {
            GuideStage::updateOrCreate(['guide_id' => $guide->id, 'stage' => $s['stage']], $s);
        }

        $myths = [
            [
                'myth_bn' => 'ধূমপান না করলে ফুসফুসের ক্যান্সার হয় না।',
                'truth_bn' => 'ধূমপান প্রধান কারণ হলেও বায়ুদূষণ, পরোক্ষ ধূমপান (Secondhand smoke) ও রান্নার ধোঁয়ার কারণে অধূমপায়ীদের মধ্যেও ফুসফুস ক্যান্সার হতে পারে।',
                'sort_order' => 1,
            ],
        ];

        foreach ($myths as $m) {
            GuideMyth::updateOrCreate(['guide_id' => $guide->id, 'myth_bn' => $m['myth_bn']], $m);
        }

        $faqs = [
            [
                'question_bn' => 'ক্রমাগত কাশি বা কফে রক্ত দেখা দিলে কি সাথে সাথে অনকোলজিস্ট দেখাবো?',
                'answer_bn' => 'হ্যাঁ, ২-৩ সপ্তাহের বেশি কাশি এবং ওষুধে না কমলে দ্রুত বুকের সিটি স্ক্যান ও বক্ষব্যাধি বা ক্যান্সার বিশেষজ্ঞের পরামর্শ নেওয়া উচিত।',
                'sort_order' => 1,
            ],
        ];

        foreach ($faqs as $f) {
            GuideFaq::updateOrCreate(['guide_id' => $guide->id, 'question_bn' => $f['question_bn']], $f);
        }
    }

    private function seedCervicalCancerGuide(Doctor $doctor): void
    {
        $cancerType = CancerType::where('slug', 'cervical-cancer')->first();
        if (!$cancerType) return;

        $cancerType->update(['guide_published' => true]);

        $guide = Guide::updateOrCreate(
            ['cancer_type_id' => $cancerType->id],
            [
                'title_bn' => 'জরায়ু মুখ ক্যান্সার — স্ক্রিনিং, লক্ষণ, স্টেজ ও চিকিৎসা গাইড',
                'intro_bn' => 'জরায়ু মুখের ক্যান্সার পুরোপুরি প্রতিরোধযোগ্য একটি রোগ। নিয়মিত প্যাপ স্মিয়ার (Pap Smear) বা VIA টেস্ট ও HPV ভ্যাকসিনের মাধ্যমে এ রোগ থেকে শতভাগ সুরক্ষা পাওয়া যায়।',
                'meta_title' => 'জরায়ু মুখ ক্যান্সার — লক্ষণ, টেস্ট ও চিকিৎসা | CancerCare Bangladesh',
                'meta_description' => 'জরায়ু মুখ ক্যান্সারের লক্ষণ, প্যাপ স্মিয়ার টেস্ট, স্টেজিং এবং সার্জারি ও রেডিওথেরাপির পূর্ণাঙ্গ বাংলা নির্দেশিকা।',
                'reviewed_by_doctor_id' => $doctor->id,
                'reviewed_at' => now()->subDays(20),
                'sources_note_bn' => 'WHO Cervical Cancer Elimination Initiative',
                'read_minutes' => 5,
                'status' => GuideStatus::Published,
                'published_at' => now()->subMonths(1),
                'last_updated_at' => now()->subDays(4),
            ]
        );

        $terms = [
            [
                'code' => 'High-Risk HPV (Type 16 & 18)',
                'slug' => 'hpv-16-18',
                'hint_bn' => 'ভাইরাস সংক্রমণ',
                'plain_explanation_bn' => 'জরায়ু মুখ ক্যান্সারের ৯৯% কারণ হলো হিউম্যান প্যাপিলোমা ভাইরাস (HPV)। এর মধ্যে ১৬ ও ১৮ নম্বর টাইপ সবচেয়ে ঝুঁকিপূর্ণ।',
                'why_matters_bn' => 'HPV টেস্টে সংক্রমণ আগে ধরা পড়লে ক্যান্সার হওয়ার ১০ বছর আগেই তা প্রতিরোধ করা যায়।',
                'scale' => null,
                'search_keywords' => 'HPV Human papillomavirus এইচপিভি ভাইরাস',
                'sort_order' => 1,
            ],
            [
                'code' => 'CIN 1 / CIN 2 / CIN 3',
                'slug' => 'cervical-intraepithelial-neoplasia',
                'hint_bn' => 'ক্যান্সার পূর্ববর্তী পর্যায়',
                'plain_explanation_bn' => 'এটি ক্যান্সার নয়, তবে চিকিৎসার অভাবে ভবিষ্যতে ক্যান্সারে রূপ নিতে পারে এমন কোষীয় পরিবর্তন। সাধারণ ছোট অপারেশনে এটি সম্পূর্ণ নির্মূল করা যায়।',
                'why_matters_bn' => 'CIN পর্যায়ে থাকলে কোনো কেমোথেরাপি লাগে না, সামান্য ডে-কেয়ার প্রসিডিউরই যথেষ্ট।',
                'scale' => null,
                'search_keywords' => 'CIN Cervical Intraepithelial Neoplasia সিআইএন',
                'sort_order' => 2,
            ],
        ];

        foreach ($terms as $t) {
            GuideTerm::updateOrCreate(['guide_id' => $guide->id, 'slug' => $t['slug']], $t);
        }

        $stages = [
            [
                'stage' => 'স্টেজ ১',
                'title_bn' => 'জরায়ুর মুখে সীমাবদ্ধ',
                'description_bn' => 'অপারেশনের (হিস্টেরেক্টমি) মাধ্যমে সম্পূর্ণ জরায়ু অপসারণ করে পূর্ণ নিরাময় সম্ভব।',
                'typical_treatment_bn' => 'র‍্যাডিক্যাল হিস্টেরেক্টমি (সার্জারি)',
                'duration_bn' => '১–২ মাস',
                'cost_min' => 90000,
                'cost_max' => 180000,
                'severity_color' => 'teal',
                'sort_order' => 1,
            ],
            [
                'stage' => 'স্টেজ ২–৩',
                'title_bn' => 'যোনিপথ বা পেলভিক দেয়ালে বিস্তৃত',
                'description_bn' => 'রেডিওথেরাপি ও ব্র্যাকিথেরাপির মাধ্যমে সফলভাবে রোগ নিরাময় করা হয়।',
                'typical_treatment_bn' => 'কেমো-রেডিওথেরাপি + ব্র্যাকিথেরাপি',
                'duration_bn' => '৩–৫ মাস',
                'cost_min' => 140000,
                'cost_max' => 260000,
                'severity_color' => 'amber',
                'sort_order' => 2,
            ],
        ];

        foreach ($stages as $s) {
            GuideStage::updateOrCreate(['guide_id' => $guide->id, 'stage' => $s['stage']], $s);
        }

        $faqs = [
            [
                'question_bn' => 'HPV ভ্যাকসিন নিলে কি বিবাহিত নারীরাও উপকৃত হবেন?',
                'answer_bn' => 'হ্যাঁ, ৪৫ বছর বয়স পর্যন্ত নারীরা চিকিৎসকের পরামর্শ নিয়ে ভ্যাকসিন নিতে পারেন, যা নতুন সংক্রমণ থেকে সুরক্ষা দেয়।',
                'sort_order' => 1,
            ],
        ];

        foreach ($faqs as $f) {
            GuideFaq::updateOrCreate(['guide_id' => $guide->id, 'question_bn' => $f['question_bn']], $f);
        }
    }

    private function seedBloodCancerGuide(Doctor $doctor): void
    {
        $cancerType = CancerType::where('slug', 'blood-cancer')->first();
        if (!$cancerType) return;

        $cancerType->update(['guide_published' => true]);

        $guide = Guide::updateOrCreate(
            ['cancer_type_id' => $cancerType->id],
            [
                'title_bn' => 'রক্তের ক্যান্সার (লিউকেমিয়া ও লিম্ফোমা) — গাইড',
                'intro_bn' => 'রক্ত ও অস্থিমজ্জার ক্যান্সারে আধুনিক কেমোথেরাপি ও অস্থিমজ্জা প্রতিস্থাপন (Bone Marrow Transplant) অত্যন্ত সফল। সঠিক সময়ে হেমাটো-অনকোলজিস্টের শরণাপন্ন হওয়া মূল চাবিকাঠি।',
                'meta_title' => 'রক্তের ক্যান্সার চিকিৎসা ও টেস্ট নির্দেশিকা | CancerCare Bangladesh',
                'meta_description' => 'লিউকেমিয়া ও লিম্ফোমার লক্ষণ, সিবিসি রিপোর্ট, বোন ম্যারো টেস্ট ও কেমোথেরাপির বিস্তারিত বাংলা গাইড।',
                'reviewed_by_doctor_id' => $doctor->id,
                'reviewed_at' => now()->subDays(25),
                'sources_note_bn' => 'ASH & NCCN Hematology Guidelines 2026',
                'read_minutes' => 6,
                'status' => GuideStatus::Published,
                'published_at' => now()->subMonths(1),
                'last_updated_at' => now()->subDays(6),
            ]
        );

        $terms = [
            [
                'code' => 'Blast Cells (%)',
                'slug' => 'blast-cells',
                'hint_bn' => 'অপরিণত শ্বেত রক্তকণিকা',
                'plain_explanation_bn' => 'রক্ত বা অস্থিমজ্জায় ২০%-এর বেশি অপরিণত ব্লাস্ট সেল থাকলে তা অ্যাকিউট লিউকেমিয়া নির্দেশ করে।',
                'why_matters_bn' => 'ব্লাস্টের ধরন (লিম্ফোব্লাস্ট নাকি মায়েলোব্লাস্ট) দেখে ALL নাকি AML তা নিশ্চিত হয়ে সুনির্দিষ্ট প্রটোকল শুরু করা হয়।',
                'scale' => null,
                'search_keywords' => 'Blast cells ব্লাস্ট সেল লিউকেমিয়া',
                'sort_order' => 1,
            ],
            [
                'code' => 'BCR-ABL1 Fusion Gene',
                'slug' => 'bcr-abl-fusion',
                'hint_bn' => 'ফিলাডেলফিয়া ক্রোমোজোম',
                'plain_explanation_bn' => 'ক্রনিক মায়েলয়েড লিউকেমিয়া (CML)-এ এই জিন মিউটেশন পাওয়া যায়। এটি থাকলে ইমাটিনিব (Imatinib) ক্যাপসুল খেয়ে রোগী ২০-৩০ বছর পুরোপুরি সুস্থ থাকেন।',
                'why_matters_bn' => 'এটি ক্যান্সারের চিকিৎসায় আধুনিক বিজ্ঞানের সবচেয়ে বড় বিপ্লব — কোনো ইনজেকশন ছাড়াই শুধুমাত্র ক্যাপসুলে রোগ নিয়ন্ত্রণে থাকে।',
                'scale' => null,
                'search_keywords' => 'BCR-ABL Philadelphia chromosome CML ইমাটিনিব',
                'sort_order' => 2,
            ],
        ];

        foreach ($terms as $t) {
            GuideTerm::updateOrCreate(['guide_id' => $guide->id, 'slug' => $t['slug']], $t);
        }

        $stages = [
            [
                'stage' => 'ইন্ডাকশন পর্যায়',
                'title_bn' => 'ক্যান্সার কোষ রক্ত থেকে সম্পূর্ণ দূর করার ধাপ',
                'description_bn' => 'হাসপাতালে ভর্তি থেকে উচ্চমাত্রার কেমোথেরাপির মাধ্যমে স্বাভাবিক রক্তকণিকা ফিরিয়ে আনা হয়।',
                'typical_treatment_bn' => 'ইনটেনসিভ কেমোথেরাপি + সাপোর্টিভ কেয়ার',
                'duration_bn' => '১–২ মাস',
                'cost_min' => 180000,
                'cost_max' => 380000,
                'severity_color' => 'teal',
                'sort_order' => 1,
            ],
        ];

        foreach ($stages as $s) {
            GuideStage::updateOrCreate(['guide_id' => $guide->id, 'stage' => $s['stage']], $s);
        }
    }

    private function seedOralCancerGuide(Doctor $doctor): void
    {
        $cancerType = CancerType::where('slug', 'oral-cancer')->first();
        if (!$cancerType) return;

        $cancerType->update(['guide_published' => true]);

        $guide = Guide::updateOrCreate(
            ['cancer_type_id' => $cancerType->id],
            [
                'title_bn' => 'মুখ ও গলার ক্যান্সার — লক্ষণ, বায়োপসি ও চিকিৎসা গাইড',
                'intro_bn' => 'তামাক, জর্দা ও গুল ব্যবহারের কারণে বাংলাদেশে মুখ ও জিহ্বার ক্যান্সার অত্যন্ত পরিচিত। প্রাথমিক ঘা অবস্থায় চিকিৎসা নিলে মুখমণ্ডল অক্ষত রেখে সম্পূর্ণ সুস্থ হওয়া সম্ভব।',
                'meta_title' => 'মুখ ও গলার ক্যান্সার চিকিৎসা নির্দেশিকা | CancerCare Bangladesh',
                'meta_description' => 'মুখের ভেতরের ঘা, জিহ্বা ও মাড়ির ক্যান্সারের লক্ষণ, বায়োপসি, সার্জারি ও প্লাস্টিক রিকনস্ট্রাকশন গাইড।',
                'reviewed_by_doctor_id' => $doctor->id,
                'reviewed_at' => now()->subDays(12),
                'sources_note_bn' => 'NCCN Head & Neck Guidelines 2026',
                'read_minutes' => 5,
                'status' => GuideStatus::Published,
                'published_at' => now()->subMonths(1),
                'last_updated_at' => now()->subDays(2),
            ]
        );

        $terms = [
            [
                'code' => 'Squamous Cell Carcinoma (SCC)',
                'slug' => 'oral-scc',
                'hint_bn' => 'মুখের ক্যান্সারের ৯৫% ধরন',
                'plain_explanation_bn' => 'মুখের ভেতরের মসৃণ আস্তরণের স্কোয়ামাস কোষ থেকে উৎপন্ন ক্যান্সার।',
                'why_matters_bn' => 'প্রাথমিক পর্যায়ে ধরা পড়লে ছোট অপারেশনে মুখের আকৃতি বা কথা বলার ক্ষমতা নষ্ট না করেই নিরাময় করা যায়।',
                'scale' => null,
                'search_keywords' => 'SCC Squamous cell carcinoma ওরাল ক্যান্সার',
                'sort_order' => 1,
            ],
        ];

        foreach ($terms as $t) {
            GuideTerm::updateOrCreate(['guide_id' => $guide->id, 'slug' => $t['slug']], $t);
        }

        $stages = [
            [
                'stage' => 'স্টেজ ১',
                'title_bn' => 'মুখের ঘা ২ সেমির কম',
                'description_bn' => 'অস্ত্রোপচারের মাধ্যমে টিউমার অপসারণ করে নিরাময় সম্ভব।',
                'typical_treatment_bn' => 'ওয়াইড এক্সিশন সার্জারি',
                'duration_bn' => '১ মাস',
                'cost_min' => 70000,
                'cost_max' => 150000,
                'severity_color' => 'teal',
                'sort_order' => 1,
            ],
        ];

        foreach ($stages as $s) {
            GuideStage::updateOrCreate(['guide_id' => $guide->id, 'stage' => $s['stage']], $s);
        }
    }
}
