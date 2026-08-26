<?php

namespace Database\Seeders;

use App\Enums\ChamberType;
use App\Enums\DoctorStatus;
use App\Models\Chamber;
use App\Models\Doctor;
use App\Models\DoctorApplication;
use App\Models\DoctorCancerTypeStage;
use App\Models\DoctorDocument;
use App\Models\DoctorPatientStory;
use App\Models\DoctorPatientTestimonial;
use App\Models\DoctorPhilosophyPoint;
use App\Models\DoctorRatingSubmission;
use App\Models\DoctorRatingSummary;
use App\Models\DoctorService;
use App\Models\DoctorStoryHighlight;
use App\Models\DoctorTimeline;
use App\Models\DoctorTreatmentSpecialty;
use App\Models\DoctorVideo;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * ডাক্তার ডোমেইনের (ধারা ৩ ও ৪) সব টেবিলে ডেমো/dummy ডেটা — শুধু ডেভেলপমেন্টে দেখার জন্য।
 * production db:seed চেইনে (DatabaseSeeder) যোগ করা হয়নি ইচ্ছাকৃতভাবে — এটা fake content।
 *
 * চালাতে: php artisan db:seed --class=DoctorDemoSeeder
 */
class DoctorDemoSeeder extends Seeder
{
    public function run(): void
    {
        $verifier = User::firstOrCreate(
            ['email' => 'verifier.demo@ccb.test'],
            ['name' => 'যাচাই কর্মকর্তা (ডেমো)', 'password' => Hash::make('password')]
        );

        $fieldAgent = User::firstOrCreate(
            ['email' => 'field.demo@ccb.test'],
            ['name' => 'মাঠকর্মী (ডেমো)', 'password' => Hash::make('password')]
        );

        $doctorTypeId = fn (string $key) => DB::table('doctor_types')->where('key', $key)->value('id');
        $cancerTypeId = fn (string $slug) => DB::table('cancer_types')->where('slug', $slug)->value('id');
        $districtId = fn (string $slug) => DB::table('districts')->where('slug', $slug)->value('id');

        // ১. ডা. সাদিয়া রহমান — published, verified, ১০টি রেটিং জমা (is_published=true হওয়ার শর্ত পূরণ)
        $sadia = $this->makeDoctor([
            'name_bn' => 'ডা. সাদিয়া রহমান',
            'name_en' => 'Sadia Rahman',
            'bmdc_number' => 'BMDC-DEMO-1001',
            'current_position_bn' => 'সহকারী অধ্যাপক, সার্জিক্যাল অনকোলজি',
            'degrees_line_bn' => 'এমবিবিএস, এফসিপিএস (সার্জারি), এমএস (সার্জিক্যাল অনকোলজি)',
            'experience_years' => 14,
            'gender' => 'female',
            'philosophy_intro_bn' => 'রোগীর সাথে কথা বলার সময়টাই সবচেয়ে গুরুত্বপূর্ণ — ভয় কমানোই প্রথম কাজ।',
            'patients_treated' => 1240,
            'offers_second_opinion' => true,
            'offers_whatsapp' => true,
            'whatsapp_fee' => 500,
            'whatsapp_response_hours' => '২৪ ঘণ্টার মধ্যে',
            'second_opinion_fee' => 1500,
            'status' => DoctorStatus::Published,
            'bmdc_verified_at' => now()->subMonths(4),
            'bmdc_verified_by' => $verifier->id,
            'doctor_approved_at' => now()->subMonths(4),
            'published_at' => now()->subMonths(4),
            'last_verified_at' => now()->subMonths(1)->toDateString(),
        ]);

        $this->attachTypes($sadia, [$doctorTypeId('surgical_oncologist')], [
            $cancerTypeId('breast-cancer') => ['is_primary' => true],
            $cancerTypeId('lung-cancer') => ['is_primary' => false],
        ]);
        $this->addProfileContent($sadia, [$cancerTypeId('breast-cancer')]);
        $this->addChamber($sadia, $districtId('dhaka'), 'বারডেম জেনারেল হাসপাতাল', ChamberType::Government);
        $this->addRatingHistory($sadia, $fieldAgent, $verifier, submissions: 10, published: true, overallScore: 4.8);
        $this->addMatchEngineData($sadia, $cancerTypeId('breast-cancer'));
        $this->addPatientContent($sadia, $cancerTypeId('breast-cancer'), $districtId('dhaka'), $districtId('sylhet'));

        // ২. ডা. কামরুল হাসান — pending_approval, verified, মাত্র ৩টি রেটিং (১০-এর কম, তাই is_published=false)
        $kamrul = $this->makeDoctor([
            'name_bn' => 'ডা. কামরুল হাসান',
            'name_en' => 'Kamrul Hasan',
            'bmdc_number' => 'BMDC-DEMO-1002',
            'current_position_bn' => 'কনসালট্যান্ট, মেডিকেল অনকোলজি',
            'degrees_line_bn' => 'এমবিবিএস, এমডি (মেডিকেল অনকোলজি)',
            'experience_years' => 9,
            'gender' => 'male',
            'philosophy_intro_bn' => 'প্রতিটি রোগীকে পুরো চিকিৎসা পরিকল্পনা বুঝিয়ে বলি, তাড়াহুড়ো করি না।',
            'patients_treated' => 480,
            'offers_second_opinion' => true,
            'offers_whatsapp' => false,
            'second_opinion_fee' => 1000,
            'status' => DoctorStatus::PendingApproval,
            'bmdc_verified_at' => now()->subDays(20),
            'bmdc_verified_by' => $verifier->id,
            'last_verified_at' => now()->subDays(20)->toDateString(),
        ]);

        $this->attachTypes($kamrul, [$doctorTypeId('medical_oncologist')], [
            $cancerTypeId('lung-cancer') => ['is_primary' => true],
        ]);
        $this->addProfileContent($kamrul, [$cancerTypeId('lung-cancer')]);
        $this->addChamber($kamrul, $districtId('chattogram'), 'চট্টগ্রাম ক্যান্সার সেন্টার', ChamberType::Private);
        $this->addRatingHistory($kamrul, $fieldAgent, $verifier, submissions: 3, published: false, overallScore: null);

        // ৩. ডা. নাজমুন নাহার — draft, এখনো BMDC যাচাই হয়নি, কোনো রেটিং নেই
        $najmun = $this->makeDoctor([
            'name_bn' => 'ডা. নাজমুন নাহার',
            'name_en' => 'Najmun Nahar',
            'bmdc_number' => 'BMDC-DEMO-1003',
            'current_position_bn' => 'রেজিস্ট্রার, পেডিয়াট্রিক অনকোলজি বিভাগ',
            'degrees_line_bn' => 'এমবিবিএস, ডিসিএইচ',
            'experience_years' => 5,
            'gender' => 'female',
            'offers_second_opinion' => false,
            'offers_whatsapp' => false,
            'status' => DoctorStatus::Draft,
        ]);

        $this->attachTypes($najmun, [$doctorTypeId('pediatric_oncologist')], [
            $cancerTypeId('childhood-cancer') => ['is_primary' => true],
        ]);
        $this->addProfileContent($najmun, [$cancerTypeId('childhood-cancer')]);

        // doctor_applications — একটা অনুমোদিত (সাদিয়ার সাথে লিঙ্ক করা), একটা পর্যালোচনাধীন, একটা নতুন জমা
        $approvedApplication = DoctorApplication::create($this->applicationPayload(
            fullName: 'সাদিয়া রহমান',
            bmdcNumber: 'BMDC-DEMO-1001',
            status: 'approved',
            reviewedBy: $verifier->id,
            reviewNote: 'সব কাগজপত্র সঠিক, BMDC যাচাই সম্পন্ন।',
        ));
        $approvedApplication->update(['doctor_id' => $sadia->id]);
        $sadia->update(['application_id' => $approvedApplication->id]);

        DoctorApplication::create($this->applicationPayload(
            fullName: 'ফাহিম আহমেদ',
            bmdcNumber: 'BMDC-DEMO-2001',
            status: 'under_review',
            reviewedBy: $verifier->id,
            reviewNote: 'BMDC সনদ যাচাই চলছে।',
        ));

        DoctorApplication::create($this->applicationPayload(
            fullName: 'তানিয়া ইসলাম',
            bmdcNumber: 'BMDC-DEMO-2002',
            status: 'submitted',
        ));
    }

    private function makeDoctor(array $attributes): Doctor
    {
        return Doctor::create(array_merge([
            'photo_path' => 'doctors/demo-placeholder.jpg',
        ], $attributes));
    }

    private function attachTypes(Doctor $doctor, array $doctorTypeIds, array $cancerTypesWithPivot): void
    {
        $doctor->doctorTypes()->sync(array_filter($doctorTypeIds));
        $doctor->cancerTypes()->sync($cancerTypesWithPivot);
    }

    private function addProfileContent(Doctor $doctor, array $cancerTypeIds): void
    {
        DoctorDocument::create([
            'doctor_id' => $doctor->id,
            'type' => 'bmdc_certificate',
            'file_path' => 'private/doctors/'.$doctor->id.'/bmdc-certificate.pdf',
            'uploaded_at' => now()->subMonths(4),
            'is_private' => true,
        ]);

        DoctorDocument::create([
            'doctor_id' => $doctor->id,
            'type' => 'degree',
            'file_path' => 'private/doctors/'.$doctor->id.'/degree.pdf',
            'uploaded_at' => now()->subMonths(4),
            'is_private' => true,
        ]);

        DoctorTimeline::create([
            'doctor_id' => $doctor->id,
            'year_label' => '২০১০',
            'title_bn' => 'এমবিবিএস সম্পন্ন',
            'institution_bn' => 'ঢাকা মেডিকেল কলেজ',
            'sort_order' => 1,
        ]);

        DoctorTimeline::create([
            'doctor_id' => $doctor->id,
            'year_label' => '২০১৬',
            'title_bn' => 'স্নাতকোত্তর ডিগ্রি সম্পন্ন',
            'institution_bn' => 'বঙ্গবন্ধু শেখ মুজিব মেডিকেল বিশ্ববিদ্যালয়',
            'sort_order' => 2,
        ]);

        DoctorPhilosophyPoint::create([
            'doctor_id' => $doctor->id,
            'icon' => 'heart-handshake',
            'title_bn' => 'সময় নিয়ে শোনা',
            'description_bn' => 'প্রতিটি রোগীর কথা মন দিয়ে শোনেন, তাড়াহুড়ো করেন না।',
            'sort_order' => 1,
        ]);

        DoctorPhilosophyPoint::create([
            'doctor_id' => $doctor->id,
            'icon' => 'message-circle',
            'title_bn' => 'সহজ ভাষায় ব্যাখ্যা',
            'description_bn' => 'জটিল মেডিকেল তথ্য সহজ বাংলায় বুঝিয়ে বলেন।',
            'sort_order' => 2,
        ]);

        foreach ($cancerTypeIds as $index => $cancerTypeId) {
            if (! $cancerTypeId) {
                continue;
            }

            DoctorService::create([
                'doctor_id' => $doctor->id,
                'cancer_type_id' => $cancerTypeId,
                'title_bn' => 'নিয়মিত ফলো-আপ ও চিকিৎসা পরিকল্পনা',
                'description_bn' => 'রোগ নির্ণয় থেকে সম্পূর্ণ চিকিৎসা পরিকল্পনা ও ফলো-আপ পর্যন্ত সেবা দেওয়া হয়।',
                'badge_text_bn' => null,
                'badge_color' => 'teal',
                'icon' => 'stethoscope',
                'sort_order' => $index + 1,
            ]);
        }

        DoctorVideo::create([
            'doctor_id' => $doctor->id,
            'type' => 'intro',
            'platform' => 'youtube',
            'video_url' => 'https://www.youtube.com/watch?v=demo-'.$doctor->id,
            'title_bn' => 'নিজের সম্পর্কে সংক্ষিপ্ত পরিচিতি',
            'description_bn' => 'কীভাবে চিকিৎসা করেন, কী গুরুত্ব দেন তার সংক্ষিপ্ত ভিডিও।',
            'duration_seconds' => 95,
            'view_count' => 320,
            'sort_order' => 1,
        ]);
    }

    private function addChamber(Doctor $doctor, ?int $districtId, string $nameBn, ChamberType $type): void
    {
        if (! $districtId) {
            return;
        }

        Chamber::create([
            'doctor_id' => $doctor->id,
            'name_bn' => $nameBn,
            'address_bn' => $nameBn.', '.($type === ChamberType::Government ? 'সরকারি ভবন' : 'প্রধান ভবন'),
            'district_id' => $districtId,
            'type' => $type,
            'fee' => $type === ChamberType::Government ? 200 : 1000,
            'days_bn' => 'রবি, মঙ্গল, বৃহস্পতি',
            'time_from' => '16:00',
            'time_to' => '20:00',
            'avg_wait_minutes' => 45,
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    private function addRatingHistory(Doctor $doctor, User $fieldAgent, User $verifier, int $submissions, bool $published, ?float $overallScore): void
    {
        for ($i = 1; $i <= $submissions; $i++) {
            DoctorRatingSubmission::create([
                'doctor_id' => $doctor->id,
                'collected_by' => $fieldAgent->id,
                'source' => 'field_hospital',
                'collection_location' => 'NICRH আউটডোর',
                'patient_phone_hash' => hash('sha256', $doctor->id.'-demo-patient-'.$i),
                'proof_type' => 'prescription',
                'answers' => [
                    'explains_clearly' => true,
                    'listens_well' => true,
                    'not_rushed' => $i % 4 !== 0,
                ],
                'is_verified' => true,
                'verified_by' => $verifier->id,
                'collected_at' => now()->subDays($submissions - $i)->toDateString(),
            ]);
        }

        if ($submissions === 0) {
            return;
        }

        DoctorRatingSummary::create([
            'doctor_id' => $doctor->id,
            'total_count' => $submissions,
            'criteria_scores' => [
                'explains_clearly' => 100,
                'listens_well' => 100,
                'not_rushed' => 75,
            ],
            'overall_score' => $overallScore,
            'is_published' => $published, // total_count >= 10 না হলে false — CLAUDE.md নীতি ৫
            'last_calculated_at' => now(),
        ]);
    }

    private function addMatchEngineData(Doctor $doctor, ?int $cancerTypeId): void
    {
        if (! $cancerTypeId) {
            return;
        }

        $stages = [
            ['stage' => '1', 'case_count' => 120, 'success_rate_percent' => 96, 'note_bn' => 'প্রাথমিক পর্যায়। স্তন রক্ষা করে অপারেশনে খুব ভালো ফল পাওয়া যায়।'],
            ['stage' => '2', 'case_count' => 340, 'success_rate_percent' => 89, 'note_bn' => 'এই স্টেজেই সবচেয়ে বেশি অভিজ্ঞ। কেমো দিয়ে টিউমার ছোট করে তারপর অপারেশন।'],
            ['stage' => '3', 'case_count' => 180, 'success_rate_percent' => 78, 'note_bn' => 'জোরালো চিকিৎসা দরকার। কেমো, অপারেশন ও রেডিয়েশন — তিনটি মিলিয়ে দলবদ্ধ পরিকল্পনা।'],
            ['stage' => '4', 'case_count' => 40, 'success_rate_percent' => 45, 'note_bn' => 'স্টেজ ৪ জটিল। এখানে জীবনের মান ভালো রাখা ও উপসর্গ কমানোই মূল লক্ষ্য।'],
            ['stage' => 'unknown', 'case_count' => 340, 'success_rate_percent' => 87, 'note_bn' => 'সব স্টেজেই কাজ করেন। প্রথমে স্টেজ নির্ণয় করাতে হবে।'],
        ];

        foreach ($stages as $row) {
            DoctorCancerTypeStage::create(array_merge($row, ['doctor_id' => $doctor->id, 'cancer_type_id' => $cancerTypeId]));
        }

        $treatments = [
            ['treatment_key' => 'surgery', 'role' => 'provides', 'note_bn' => 'সার্জিক্যাল অনকোলজিস্ট হিসেবে নিজেই করেন। ২৮০+ স্তন অপারেশন।'],
            ['treatment_key' => 'chemo', 'role' => 'provides', 'note_bn' => 'মেডিকেল অনকোলজিস্ট হিসেবে কেমো দেন — অপারেশনের আগে ও পরে দুটোই।'],
            ['treatment_key' => 'radiation', 'role' => 'refers', 'note_bn' => 'রেডিয়েশন নিজে দেন না — রেডিয়েশন বিশেষজ্ঞের কাছে পাঠান এবং একসাথে কাজ করেন।'],
            ['treatment_key' => 'hormone', 'role' => 'provides', 'note_bn' => 'হরমোন থেরাপি নিজেই দেন।'],
            ['treatment_key' => 'unknown', 'role' => 'provides', 'note_bn' => 'স্টেজ দেখে চিকিৎসা ঠিক হবে।'],
        ];

        foreach ($treatments as $row) {
            DoctorTreatmentSpecialty::create(array_merge($row, ['doctor_id' => $doctor->id, 'cancer_type_id' => $cancerTypeId]));
        }

        DoctorVideo::create([
            'doctor_id' => $doctor->id,
            'type' => 'educational',
            'platform' => 'youtube',
            'video_url' => 'https://www.youtube.com/watch?v=demo-edu-'.$doctor->id.'-1',
            'title_bn' => 'স্তন ক্যান্সারের প্রথম লক্ষণ — কখন ডাক্তার দেখাবেন',
            'duration_seconds' => 272,
            'view_count' => 12400,
            'sort_order' => 2,
        ]);

        DoctorVideo::create([
            'doctor_id' => $doctor->id,
            'type' => 'educational',
            'platform' => 'facebook',
            'video_url' => 'https://www.facebook.com/watch/?v=demo-edu-'.$doctor->id.'-2',
            'title_bn' => 'কেমোথেরাপি আসলে কতটা কষ্টকর',
            'duration_seconds' => 434,
            'view_count' => 8900,
            'sort_order' => 3,
        ]);
    }

    private function addPatientContent(Doctor $doctor, ?int $cancerTypeId, ?int $dhakaDistrictId, ?int $sylhetDistrictId): void
    {
        DoctorPatientTestimonial::create([
            'doctor_id' => $doctor->id,
            'cancer_type_id' => $cancerTypeId,
            'stage' => '3',
            'outcome_bn' => 'ক্যান্সার-মুক্ত',
            'anonymized_label_bn' => 'নারী, ৪০-এর কোঠায়',
            'year' => 2023,
            'video_url' => 'https://www.youtube.com/watch?v=demo-testimonial-'.$doctor->id.'-1',
            'duration_seconds' => 48,
            'thumbnail_color_key' => 'teal',
            'sort_order' => 1,
        ]);

        DoctorPatientTestimonial::create([
            'doctor_id' => $doctor->id,
            'cancer_type_id' => $cancerTypeId,
            'stage' => '2',
            'outcome_bn' => 'ক্যান্সার-মুক্ত',
            'anonymized_label_bn' => 'নারী, ৩০-এর কোঠায়',
            'year' => 2022,
            'video_url' => 'https://www.youtube.com/watch?v=demo-testimonial-'.$doctor->id.'-2',
            'duration_seconds' => 72,
            'thumbnail_color_key' => 'pink',
            'sort_order' => 2,
        ]);

        DoctorStoryHighlight::create(['doctor_id' => $doctor->id, 'label_bn' => '১২১ জন স্টেজ ৩ থেকে সুস্থ', 'color_key' => 'teal', 'sort_order' => 1]);
        DoctorStoryHighlight::create(['doctor_id' => $doctor->id, 'label_bn' => '৩৪০টি যাচাইকৃত কেস', 'color_key' => 'blue', 'sort_order' => 2]);

        DoctorPatientStory::create([
            'doctor_id' => $doctor->id,
            'cancer_type_id' => $cancerTypeId,
            'stage' => '3',
            'district_id' => $dhakaDistrictId,
            'patient_label_bn' => 'রাজিয়া বেগম',
            'year' => 2022,
            'outcome_duration_bn' => '২ বছর ক্যান্সার-মুক্ত',
            'quote_bn' => 'স্টেজ ৩ শুনে ভেঙে পড়েছিলাম। ডা. রহমান বললেন — আপনার ভয়টা স্বাভাবিক, কিন্তু এটার সাথে লড়া যায়। সেই কথাটাই আমাকে ধরে রেখেছিল।',
            'then_bn' => 'হাঁটতে পারতেন না',
            'now_bn' => 'কাজে ফিরেছেন',
            'is_name_changed' => false,
            'is_family_told' => false,
            'sort_order' => 1,
        ]);

        DoctorPatientStory::create([
            'doctor_id' => $doctor->id,
            'cancer_type_id' => $cancerTypeId,
            'stage' => '2',
            'district_id' => $sylhetDistrictId,
            'patient_label_bn' => 'মোহাম্মদ করিম',
            'year' => 2023,
            'outcome_duration_bn' => '১ বছর ক্যান্সার-মুক্ত',
            'quote_bn' => 'আমরা সিলেটে থাকি। ডা. রহমান WhatsApp-এ রিপোর্ট দেখে বললেন ঢাকায় আসতে। দুই মাসেই চিকিৎসা শেষ। মা আজ সুস্থ।',
            'then_bn' => 'টিউমার ৩ সেমি',
            'now_bn' => 'স্ক্যান পরিষ্কার',
            'is_name_changed' => false,
            'is_family_told' => true,
            'sort_order' => 2,
        ]);
    }

    private function applicationPayload(string $fullName, string $bmdcNumber, string $status, ?int $reviewedBy = null, ?string $reviewNote = null): array
    {
        return [
            'full_name' => $fullName,
            'bmdc_number' => $bmdcNumber,
            'phone' => '01700000000',
            'email' => strtolower(str_replace(' ', '.', $fullName)).'@example.com',
            'photo_path' => 'applications/demo-placeholder.jpg',
            'degrees' => [['degree' => 'এমবিবিএস', 'institution_bn' => 'ঢাকা মেডিকেল কলেজ', 'year' => 2010]],
            'timeline' => [['year_label' => '২০১০', 'title_bn' => 'এমবিবিএস সম্পন্ন']],
            'doctor_type_ids' => [],
            'cancer_type_ids' => [],
            'chambers' => [],
            'extra_services' => ['second_opinion' => true, 'whatsapp' => false],
            'preferred_call_time' => 'বিকাল ৪টা - ৬টা',
            'preferred_call_day' => 'রবি, মঙ্গল',
            'declarations' => ['information_accurate' => true, 'agreed_at' => now()->toDateTimeString()],
            'status' => $status,
            'reviewed_by' => $reviewedBy,
            'review_note' => $reviewNote,
        ];
    }
}
