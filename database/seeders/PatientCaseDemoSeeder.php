<?php

namespace Database\Seeders;

use App\Enums\PatientCaseAccountType;
use App\Enums\PatientCaseAnonymity;
use App\Enums\PatientCaseDocumentType;
use App\Enums\PatientCaseGender;
use App\Enums\PatientCaseStatus;
use App\Enums\PatientCaseVerificationStatus;
use App\Enums\PatientCaseVerificationStep;
use App\Models\CancerType;
use App\Models\District;
use App\Models\Hospital;
use App\Models\PatientCase;
use App\Models\PatientCaseAccount;
use App\Models\PatientCaseCost;
use App\Models\PatientCaseDocument;
use App\Models\PatientCaseUpdate;
use App\Models\PatientCaseVerification;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PatientCaseDemoSeeder extends Seeder
{
    public function run(): void
    {
        $verifier = User::firstOrCreate(
            ['email' => 'verifier.demo@ccb.test'],
            ['name' => 'যাচাই কর্মকর্তা (ডেমো)', 'password' => Hash::make('password')]
        );
        $verifier->assignRole('verification_officer');

        $fieldAgent = User::firstOrCreate(
            ['email' => 'field.demo@ccb.test'],
            ['name' => 'মাঠকর্মী (ডেমো)', 'password' => Hash::make('password')]
        );
        $fieldAgent->assignRole('field_agent');

        $cancerTypeId = fn (string $slug) => CancerType::where('slug', $slug)->value('id') ?? CancerType::first()->id;
        $districtId = fn (string $slug) => District::where('slug', $slug)->value('id') ?? District::first()->id;
        $hospitalId = fn (string $slug) => Hospital::where('slug', $slug)->value('id');

        $year = Carbon::now()->format('Y');

        // =========================================================================
        // CASE 1: মোছা. সালমা খাতুন (স্তন ক্যান্সার, প্রকাশিত, সম্পূর্ণ যাচাইকৃত)
        // =========================================================================
        $case1 = PatientCase::updateOrCreate(
            ['case_code' => "CCB-{$year}-0001"],
            [
                'real_name' => 'মোছা. সালমা খাতুন',
                'display_name_bn' => 'সালমা বেগম',
                'age' => 42,
                'gender' => PatientCaseGender::Female,
                'cancer_type_id' => $cancerTypeId('breast-cancer'),
                'stage' => 'স্টেজ ৩',
                'district_id' => $districtId('bogra') ?? $districtId('dhaka-dist'),
                'hospital_id' => $hospitalId('nicrh') ?? Hospital::first()?->id,
                'treating_doctor_name' => 'অধ্যাপক ডা. মোফাজ্জল হোসেন',
                'story_bn' => "আমার স্বামী বগুড়ার একটি বাজারে দিনমজুরের কাজ করেন। প্রায় সাত মাস আগে আমার ডান স্তনে একটি শক্ত চাকা অনুভব করি। স্থানীয় ক্লিনিকে দেখানোর পর তারা ঢাকায় জাতীয় ক্যান্সার ইনস্টিটিউটে পাঠান। সেখানে কোর বায়োপসি এবং ম্যামোগ্রাফিতে স্টেজ ৩ ইনভেসিভ ডাক্টাল কার্সিনোমা ধরা পড়ে।\n\nডাক্তাররা জানিয়েছেন, দ্রুত ৬ সাইকেল নিওঅ্যাডজুভেন্ট কেমোথেরাপির পর মডিফাইড র‍্যাডিক্যাল ম্যাস্টেক্টমি অপারেশন এবং এরপর রেডিওথেরাপি দিতে হবে। দিনমজুর স্বামীর সামান্য আয়ে সংসার চালানোই কঠিন, চিকিৎসার প্রায় পৌনে দুই লাখ টাকা জোগাড় করা আমাদের পক্ষে অসম্ভব হয়ে পড়েছে। আপনাদের সামান্য সহানুভূতি আমার সন্তানদের মুখে হাসি ফিরিয়ে দিতে পারে।",
                'amount_needed' => 185000,
                'photo_path' => 'patient-cases/photos/salma_demo.jpg',
                'show_photo' => true,
                'anonymity_level' => PatientCaseAnonymity::FullName,
                'consent_form_path' => 'patient-cases/consent/salma_consent.pdf',
                'consent_signed_at' => Carbon::now()->subDays(5),
                'status' => PatientCaseStatus::Published,
                'verified_at' => Carbon::now()->subDays(4),
                'published_at' => Carbon::now()->subDays(4),
                'expires_at' => Carbon::now()->addDays(26),
                'created_by' => $fieldAgent->id,
                'verified_by' => $verifier->id,
            ]
        );

        $this->seedVerifications($case1, [
            PatientCaseVerificationStep::Documents->value => [
                'status' => PatientCaseVerificationStatus::Done,
                'note' => 'এনআইসিআরএইচ-এর হিস্টোপ্যাথলজি ও ম্যামোগ্রাফি রিপোর্ট সরাসরি চিকিৎসকের সিলমোহর সহ যাচাই করা হয়েছে।',
            ],
            PatientCaseVerificationStep::HospitalConfirm->value => [
                'status' => PatientCaseVerificationStatus::Done,
                'note' => 'হাসপাতালের আউটডোর টিকিট ও অনকোলজি ওয়ার্ডে ভর্তি রেজিস্ট্রেশন নিশ্চিত করা হয়েছে।',
            ],
            PatientCaseVerificationStep::Identity->value => [
                'status' => PatientCaseVerificationStatus::Done,
                'note' => 'নির্বাচন কমিশনের সার্ভারে জাতীয় পরিচয়পত্র নম্বর ও জন্মতারিখ মিলিয়ে দেখা হয়েছে।',
            ],
            PatientCaseVerificationStep::FieldMeeting->value => [
                'status' => PatientCaseVerificationStatus::Done,
                'note' => 'বগুড়ার শেরপুরস্থ ঠিকানায় মাঠকর্মী সরেজমিনে গিয়ে পরিবারের আর্থিক অসচ্ছলতা নিশ্চিত করেছেন।',
            ],
        ], $verifier->id);

        $this->seedAccounts($case1, [
            [
                'type' => PatientCaseAccountType::Bkash,
                'account_number' => '01712-345678',
                'account_name' => 'মোছা. সালমা খাতুন',
                'name_verified' => true,
                'is_active' => true,
            ],
            [
                'type' => PatientCaseAccountType::Nagad,
                'account_number' => '01812-345678',
                'account_name' => 'মোছা. সালমা খাতুন',
                'name_verified' => true,
                'is_active' => true,
            ],
        ]);

        $this->seedCosts($case1, [
            ['item_bn' => '৬ সাইকেল কেমোথেরাপি ওষুধ ও স্যালাইন', 'amount' => 90000, 'sort_order' => 1],
            ['item_bn' => 'মডিফাইড র‍্যাডিক্যাল ম্যাস্টেক্টমি (এমআরএম) সার্জারি', 'amount' => 45000, 'sort_order' => 2],
            ['item_bn' => '২৫ সেশন রেডিওথেরাপি চার্জ ও সাপোর্ট ওষুধ', 'amount' => 50000, 'sort_order' => 3],
        ]);

        $this->seedDocuments($case1, [
            ['type' => PatientCaseDocumentType::Biopsy, 'file_path' => 'documents/salma_biopsy.pdf', 'is_public' => true, 'redacted' => true],
            ['type' => PatientCaseDocumentType::TreatmentPlan, 'file_path' => 'documents/salma_plan.pdf', 'is_public' => true, 'redacted' => true],
            ['type' => PatientCaseDocumentType::CostEstimate, 'file_path' => 'documents/salma_cost.pdf', 'is_public' => true, 'redacted' => true],
            ['type' => PatientCaseDocumentType::Nid, 'file_path' => 'documents/salma_nid.pdf', 'is_public' => false, 'redacted' => true],
        ], $fieldAgent->id);

        PatientCaseUpdate::updateOrCreate(
            ['patient_case_id' => $case1->id, 'note_bn' => 'প্রথম ২টি কেমোথেরাপির সাইকেল সফলভাবে সম্পন্ন হয়েছে। রোগী বর্তমানে স্থিতিশীল আছেন।'],
            ['update_date' => Carbon::now()->subDays(2), 'created_by' => $fieldAgent->id, 'is_public' => true]
        );

        // =========================================================================
        // CASE 2: শিশু আরিয়ান (লিউকেমিয়া / রক্ত ক্যান্সার, শিশু সুরক্ষা নীতি প্রযোজ্য)
        // =========================================================================
        $case2 = PatientCase::updateOrCreate(
            ['case_code' => "CCB-{$year}-0002"],
            [
                'real_name' => 'আরিয়ান আহমেদ',
                'display_name_bn' => 'শিশু আরিয়ান',
                'age' => 7,
                'gender' => PatientCaseGender::Male,
                'cancer_type_id' => $cancerTypeId('blood-cancer') ?? $cancerTypeId('leukemia'),
                'stage' => 'স্ট্যান্ডার্ড রিস্ক ALL',
                'district_id' => $districtId('dhaka-dist'),
                'hospital_id' => $hospitalId('dmch') ?? Hospital::first()?->id,
                'treating_doctor_name' => 'ডা. আফরোজা বেগম',
                'story_bn' => "৭ বছর বয়সী আরিয়ান দ্বিতীয় শ্রেণিতে পড়ে। গত তিন মাস ধরে ঘন ঘন জ্বর, শরীর ফ্যাকাশে হওয়া ও দাঁতের মাড়ি দিয়ে রক্ত পড়ার লক্ষণ দেখা দেয়। ঢাকা মেডিকেল কলেজ হাসপাতালের হেমাটোলজি বিভাগে বোন ম্যারো টেস্টের পর তার একিউট লিম্ফোব্লাস্টিক লিউকেমিয়া (ALL) ধরা পড়ে।\n\nচিকিৎসকদের মতে, শিশুদের রক্তের ক্যান্সার সময়মতো সম্পূর্ণ প্রোটোকল মেনে চিকিৎসা করালে ৮০-৯০% ক্ষেত্রে সম্পূর্ণ নিরাময় সম্ভব। দীর্ঘ দুই বছরের ইনটেনসিভ ও মেনটেইনেন্স কেমোথেরাপির খরচ সাড়ে তিন লাখ টাকা। শিশুটির পিতা একজন ক্ষুদ্র চা দোকানী। সন্তানের জীবন রক্ষায় সমাজের দানশীল মানুষের সাহায্য একান্ত কাম্য।",
                'amount_needed' => 350000,
                'photo_path' => 'patient-cases/photos/child_aryan.jpg',
                'show_photo' => false, // শিশু সুরক্ষা নীতি: বয়স ১৮-এর নিচে হলে বাধ্যতামূলক false
                'anonymity_level' => PatientCaseAnonymity::ChangedName,
                'consent_form_path' => 'patient-cases/consent/aryan_parent_consent.pdf',
                'consent_signed_at' => Carbon::now()->subDays(3),
                'status' => PatientCaseStatus::Published,
                'verified_at' => Carbon::now()->subDays(2),
                'published_at' => Carbon::now()->subDays(2),
                'expires_at' => Carbon::now()->addDays(28),
                'created_by' => $fieldAgent->id,
                'verified_by' => $verifier->id,
            ]
        );

        $this->seedVerifications($case2, [
            PatientCaseVerificationStep::Documents->value => [
                'status' => PatientCaseVerificationStatus::Done,
                'note' => 'ঢাকা মেডিকেল কলেজের বোন ম্যারো ও ফ্লো সাইটোমেট্রি রিপোর্ট যাচাইকৃত।',
            ],
            PatientCaseVerificationStep::HospitalConfirm->value => [
                'status' => PatientCaseVerificationStatus::Done,
                'note' => 'শিশু হেমাটো-অনকোলজি বহির্বিভাগে চলমান চিকিৎসা কার্ড ও বেড নম্বর নিশ্চিত।',
            ],
            PatientCaseVerificationStep::Identity->value => [
                'status' => PatientCaseVerificationStatus::Done,
                'note' => 'পিতার এনআইডি ও সন্তানের ডিজিটাল জন্মসনদ যাচাই সম্পন্ন।',
            ],
            PatientCaseVerificationStep::FieldMeeting->value => [
                'status' => PatientCaseVerificationStatus::Done,
                'note' => 'হাসপাতালে সরাসরি শিশু ও তার পিতা-মাতার সাথে সাক্ষাৎ করে আর্থিক তথ্য যাচাই করা হয়েছে।',
            ],
        ], $verifier->id);

        $this->seedAccounts($case2, [
            [
                'type' => PatientCaseAccountType::Bank,
                'account_number' => '115.120.987654',
                'account_name' => 'মো. ফজলুল হক (পিতা)',
                'bank_name' => 'ডাচ-বাংলা ব্যাংক পিএলসি',
                'branch' => 'মিরপুর শাখা, ঢাকা',
                'name_verified' => true,
                'is_active' => true,
            ],
            [
                'type' => PatientCaseAccountType::Bkash,
                'account_number' => '01911-223344',
                'account_name' => 'মো. ফজলুল হক',
                'name_verified' => true,
                'is_active' => true,
            ],
        ]);

        $this->seedCosts($case2, [
            ['item_bn' => 'ইন্ডাকশন ও কনসোলিডেশন থেরাপি ওষুধ', 'amount' => 140000, 'sort_order' => 1],
            ['item_bn' => 'রক্ত উপাদান (PRBC, Platelets) ও অ্যান্টিবায়োটিক সাপোর্ট', 'amount' => 90000, 'sort_order' => 2],
            ['item_bn' => 'মেনটেইনেন্স কেমোথেরাপি ও নিয়মিত ল্যাব মনিটরিং (১ম বছর)', 'amount' => 120000, 'sort_order' => 3],
        ]);

        $this->seedDocuments($case2, [
            ['type' => PatientCaseDocumentType::Biopsy, 'file_path' => 'documents/aryan_bonemarrow.pdf', 'is_public' => true, 'redacted' => true],
            ['type' => PatientCaseDocumentType::TreatmentPlan, 'file_path' => 'documents/aryan_protocol.pdf', 'is_public' => true, 'redacted' => true],
            ['type' => PatientCaseDocumentType::CostEstimate, 'file_path' => 'documents/aryan_estimate.pdf', 'is_public' => true, 'redacted' => true],
        ], $fieldAgent->id);

        // =========================================================================
        // CASE 3: মো. জাহাঙ্গীর আলম (ফুসফুসের ক্যান্সার, প্রকাশিত)
        // =========================================================================
        $case3 = PatientCase::updateOrCreate(
            ['case_code' => "CCB-{$year}-0003"],
            [
                'real_name' => 'মো. জাহাঙ্গীর আলম',
                'display_name_bn' => 'জাহাঙ্গীর আলম',
                'age' => 54,
                'gender' => PatientCaseGender::Male,
                'cancer_type_id' => $cancerTypeId('lung-cancer'),
                'stage' => 'স্টেজ ২B',
                'district_id' => $districtId('mymensingh') ?? $districtId('dhaka-dist'),
                'hospital_id' => $hospitalId('nicrh') ?? Hospital::first()?->id,
                'treating_doctor_name' => 'ডা. সাজিদ করিম',
                'story_bn' => "ময়মনসিংহের একটি গ্যারেজে মেকানিকের কাজ করতেন জাহাঙ্গীর সাহেব। গত ছয় মাস ধরে শ্বাসকষ্ট ও কাশির সাথে রক্ত যাওয়ায় ব্রঙ্কোস্কোপি ও সিটি গাইডেড বায়োপসিতে ফুসফুসে ক্যান্সার ধরা পড়ে। দ্রুত থোরাসিক সার্জারি এবং কেমোথেরাপির মাধ্যমে সুস্থ হয়ে ওঠা সম্ভব। পরিবারের উপার্জনক্ষম ব্যক্তি অসুস্থ হওয়ায় চিকিৎসার ২ লাখ ২০ হাজার টাকা জোগাড় করা অত্যন্ত দুরূহ হয়ে পড়েছে।",
                'amount_needed' => 220000,
                'photo_path' => 'patient-cases/photos/jahangir_demo.jpg',
                'show_photo' => true,
                'anonymity_level' => PatientCaseAnonymity::FullName,
                'consent_form_path' => 'patient-cases/consent/jahangir_consent.pdf',
                'consent_signed_at' => Carbon::now()->subDays(8),
                'status' => PatientCaseStatus::Published,
                'verified_at' => Carbon::now()->subDays(7),
                'published_at' => Carbon::now()->subDays(7),
                'expires_at' => Carbon::now()->addDays(23),
                'created_by' => $fieldAgent->id,
                'verified_by' => $verifier->id,
            ]
        );

        $this->seedVerifications($case3, [
            PatientCaseVerificationStep::Documents->value => ['status' => PatientCaseVerificationStatus::Done, 'note' => 'সিটি গাইডেড এফএনএসি ও হিস্টোলজি রিপোর্ট নিশ্চিত।'],
            PatientCaseVerificationStep::HospitalConfirm->value => ['status' => PatientCaseVerificationStatus::Done, 'note' => 'থোরাসিক সার্জারি বিভাগের সিডিউল নিশ্চিত।'],
            PatientCaseVerificationStep::Identity->value => ['status' => PatientCaseVerificationStatus::Done, 'note' => 'এনআইডি সার্ভার ভেরিফিকেশন সম্পন্ন।'],
            PatientCaseVerificationStep::FieldMeeting->value => ['status' => PatientCaseVerificationStatus::Done, 'note' => 'ময়মনসিংহে রোগীর বাসায় সরেজমিনে তথ্য যাচাই।'],
        ], $verifier->id);

        $this->seedAccounts($case3, [
            [
                'type' => PatientCaseAccountType::Nagad,
                'account_number' => '01633-445566',
                'account_name' => 'মো. জাহাঙ্গীর আলম',
                'name_verified' => true,
                'is_active' => true,
            ],
            [
                'type' => PatientCaseAccountType::Rocket,
                'account_number' => '01633-445566-7',
                'account_name' => 'মো. জাহাঙ্গীর আলম',
                'name_verified' => true,
                'is_active' => true,
            ],
        ]);

        $this->seedCosts($case3, [
            ['item_bn' => 'লোবেক্টমি (ফুসফুস) অপারেশন ও ওটি চার্জ', 'amount' => 130000, 'sort_order' => 1],
            ['item_bn' => 'পোস্ট-সার্জারি এডজুভেন্ট কেমোথেরাপি ৪ সাইকেল', 'amount' => 90000, 'sort_order' => 2],
        ]);

        $this->seedDocuments($case3, [
            ['type' => PatientCaseDocumentType::Biopsy, 'file_path' => 'documents/jahangir_biopsy.pdf', 'is_public' => true, 'redacted' => true],
            ['type' => PatientCaseDocumentType::CostEstimate, 'file_path' => 'documents/jahangir_cost.pdf', 'is_public' => true, 'redacted' => true],
        ], $fieldAgent->id);

        // =========================================================================
        // CASE 4: মোছা. রাবেয়া বেগম (জরায়ু মুখ ক্যান্সার, যাচাই প্রক্রিয়াধীন - ৩/৪ ধাপ শেষ)
        // =========================================================================
        $case4 = PatientCase::updateOrCreate(
            ['case_code' => "CCB-{$year}-0004"],
            [
                'real_name' => 'মোছা. রাবেয়া বেগম',
                'display_name_bn' => 'রাবেয়া বেগম',
                'age' => 46,
                'gender' => PatientCaseGender::Female,
                'cancer_type_id' => $cancerTypeId('cervical-cancer'),
                'stage' => 'স্টেজ ২B',
                'district_id' => $districtId('rangpur') ?? $districtId('dhaka-dist'),
                'hospital_id' => Hospital::first()?->id,
                'treating_doctor_name' => 'ডা. নাজনীন সুলতানা',
                'story_bn' => 'রংপুরের বদরগঞ্জ উপজেলার বাসিন্দা রাবেয়া বেগম। হিস্টোপ্যাথলজিতে জরায়ু মুখের ক্যান্সার ধরা পড়েছে। কনকারেন্ট কেমো-রেডিওথেরাপি ও ব্র্যাকিথেরাপির জন্য রংপুর মেডিকেল থেকে ঢাকায় রেফার করা হয়েছে। চিকিৎসার প্রায় দেড় লাখ টাকা প্রয়োজন।',
                'amount_needed' => 140000,
                'photo_path' => 'patient-cases/photos/rabeya_demo.jpg',
                'show_photo' => true,
                'anonymity_level' => PatientCaseAnonymity::FullName,
                'consent_form_path' => 'patient-cases/consent/rabeya_consent.pdf',
                'consent_signed_at' => Carbon::now()->subDays(2),
                'status' => PatientCaseStatus::Verifying,
                'created_by' => $fieldAgent->id,
            ]
        );

        $this->seedVerifications($case4, [
            PatientCaseVerificationStep::Documents->value => ['status' => PatientCaseVerificationStatus::Done, 'note' => 'বায়োপসি ও সিটি স্ক্যান রিপোর্ট যাচাইকৃত।'],
            PatientCaseVerificationStep::HospitalConfirm->value => ['status' => PatientCaseVerificationStatus::Done, 'note' => 'রেডিওথেরাপি বিভাগের প্রাক্কলন ও অ্যাপয়েন্টমেন্ট যাচাইকৃত।'],
            PatientCaseVerificationStep::Identity->value => ['status' => PatientCaseVerificationStatus::Done, 'note' => 'এনআইডি কার্ডের তথ্য সার্ভারে যাচাইকৃত।'],
            PatientCaseVerificationStep::FieldMeeting->value => ['status' => PatientCaseVerificationStatus::Pending, 'note' => 'মাঠপর্যায়ে সরাসরি সাক্ষাৎ ও প্রতিবেদন অপেক্ষমান।'],
        ], $verifier->id);

        $this->seedAccounts($case4, [
            [
                'type' => PatientCaseAccountType::Bkash,
                'account_number' => '01777-889900',
                'account_name' => 'মোছা. রাবেয়া বেগম',
                'name_verified' => true,
                'is_active' => true,
            ],
        ]);

        $this->seedCosts($case4, [
            ['item_bn' => 'কনকারেন্ট কেমো-রেডিওথেরাপি ২৫ সেশন', 'amount' => 95000, 'sort_order' => 1],
            ['item_bn' => 'আইসিআরটি ব্র্যাকিথেরাপি ৩ সেশন', 'amount' => 45000, 'sort_order' => 2],
        ]);

        $this->seedDocuments($case4, [
            ['type' => PatientCaseDocumentType::Biopsy, 'file_path' => 'documents/rabeya_biopsy.pdf', 'is_public' => true, 'redacted' => true],
        ], $fieldAgent->id);

        // =========================================================================
        // CASE 5: খোরশেদ মিয়া (কোলন ক্যান্সার, খসড়া / নতুন আবেদন)
        // =========================================================================
        $case5 = PatientCase::updateOrCreate(
            ['case_code' => "CCB-{$year}-0005"],
            [
                'real_name' => 'মো. খোরশেদ মিয়া',
                'display_name_bn' => 'খোরশেদ আলম',
                'age' => 58,
                'gender' => PatientCaseGender::Male,
                'cancer_type_id' => $cancerTypeId('colorectal-cancer') ?? $cancerTypeId('colon-cancer'),
                'stage' => 'স্টেজ ৩',
                'district_id' => $districtId('comilla') ?? $districtId('dhaka-dist'),
                'story_bn' => 'কোলনোস্কোপি ও বায়োপসিতে কোলোরেক্টাল ক্যান্সার ধরা পড়েছে। সার্জারি ও কেমোথেরাপির প্যাকেজের জন্য আর্থিক আবেদন।',
                'amount_needed' => 175000,
                'show_photo' => false,
                'anonymity_level' => PatientCaseAnonymity::Partial,
                'status' => PatientCaseStatus::Draft,
                'created_by' => $fieldAgent->id,
            ]
        );

        $this->seedVerifications($case5, [
            PatientCaseVerificationStep::Documents->value => ['status' => PatientCaseVerificationStatus::Pending, 'note' => null],
            PatientCaseVerificationStep::HospitalConfirm->value => ['status' => PatientCaseVerificationStatus::Pending, 'note' => null],
            PatientCaseVerificationStep::Identity->value => ['status' => PatientCaseVerificationStatus::Pending, 'note' => null],
            PatientCaseVerificationStep::FieldMeeting->value => ['status' => PatientCaseVerificationStatus::Pending, 'note' => null],
        ], $verifier->id);

        $this->seedCosts($case5, [
            ['item_bn' => 'হিমিকোলেক্টমি অপারেশন ও হিস্টোলজি', 'amount' => 100000, 'sort_order' => 1],
            ['item_bn' => 'কেমোথেরাপি ও সাপোর্টিভ মেডিসিন', 'amount' => 75000, 'sort_order' => 2],
        ]);

        // =========================================================================
        // CASE 6: মোছা. সাজেদা খাতুন (মেয়াদোত্তীর্ণ কেস)
        // =========================================================================
        $case6 = PatientCase::updateOrCreate(
            ['case_code' => "CCB-{$year}-0006"],
            [
                'real_name' => 'মোছা. সাজেদা খাতুন',
                'display_name_bn' => 'সাজেদা খাতুন',
                'age' => 49,
                'gender' => PatientCaseGender::Female,
                'cancer_type_id' => $cancerTypeId('oral-cancer') ?? $cancerTypeId('breast-cancer'),
                'stage' => 'স্টেজ ২',
                'district_id' => $districtId('sylhet') ?? $districtId('dhaka-dist'),
                'story_bn' => 'মুখের ভেতরের ঘা থেকে ক্যান্সার শনাক্ত হয়। সফল চিকিৎসার পর বর্তমানে ফলো-আপে রয়েছেন।',
                'amount_needed' => 110000,
                'photo_path' => 'patient-cases/photos/sajeda_demo.jpg',
                'show_photo' => true,
                'anonymity_level' => PatientCaseAnonymity::FullName,
                'consent_form_path' => 'patient-cases/consent/sajeda_consent.pdf',
                'consent_signed_at' => Carbon::now()->subDays(40),
                'status' => PatientCaseStatus::Expired,
                'verified_at' => Carbon::now()->subDays(35),
                'published_at' => Carbon::now()->subDays(35),
                'expires_at' => Carbon::now()->subDays(5),
                'created_by' => $fieldAgent->id,
                'verified_by' => $verifier->id,
            ]
        );

        $this->seedVerifications($case6, [
            PatientCaseVerificationStep::Documents->value => ['status' => PatientCaseVerificationStatus::Done, 'note' => 'যাচাইকৃত।'],
            PatientCaseVerificationStep::HospitalConfirm->value => ['status' => PatientCaseVerificationStatus::Done, 'note' => 'যাচাইকৃত।'],
            PatientCaseVerificationStep::Identity->value => ['status' => PatientCaseVerificationStatus::Done, 'note' => 'যাচাইকৃত।'],
            PatientCaseVerificationStep::FieldMeeting->value => ['status' => PatientCaseVerificationStatus::Done, 'note' => 'যাচাইকৃত।'],
        ], $verifier->id);

        $this->seedAccounts($case6, [
            [
                'type' => PatientCaseAccountType::Bkash,
                'account_number' => '01555-667788',
                'account_name' => 'মোছা. সাজেদা খাতুন',
                'name_verified' => true,
                'is_active' => true,
            ],
        ]);
    }

    private function seedVerifications(PatientCase $case, array $stepsData, int $userId): void
    {
        foreach ($stepsData as $stepKey => $data) {
            PatientCaseVerification::updateOrCreate(
                ['patient_case_id' => $case->id, 'step' => $stepKey],
                [
                    'status' => $data['status'],
                    'note_bn' => $data['note'],
                    'completed_by' => $userId,
                    'completed_at' => ($data['status'] === PatientCaseVerificationStatus::Done) ? Carbon::now() : null,
                ]
            );
        }
    }

    private function seedAccounts(PatientCase $case, array $accountsData): void
    {
        foreach ($accountsData as $account) {
            PatientCaseAccount::updateOrCreate(
                ['patient_case_id' => $case->id, 'account_number' => $account['account_number']],
                $account
            );
        }
    }

    private function seedCosts(PatientCase $case, array $costsData): void
    {
        foreach ($costsData as $cost) {
            PatientCaseCost::updateOrCreate(
                ['patient_case_id' => $case->id, 'item_bn' => $cost['item_bn']],
                $cost
            );
        }
    }

    private function seedDocuments(PatientCase $case, array $docsData, int $userId): void
    {
        foreach ($docsData as $doc) {
            PatientCaseDocument::updateOrCreate(
                ['patient_case_id' => $case->id, 'file_path' => $doc['file_path']],
                array_merge($doc, ['uploaded_by' => $userId, 'uploaded_at' => Carbon::now()])
            );
        }
    }
}
