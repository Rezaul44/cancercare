<?php

namespace Tests\Feature;

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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuideDetailTest extends TestCase
{
    use RefreshDatabase;

    private function createPublishedGuide(): array
    {
        $doctor = Doctor::create([
            'name_bn' => 'ডা. সাদিয়া রহমান',
            'name_en' => 'Dr. Sadia Rahman',
            'slug' => 'dr-sadia-rahman-'.uniqid(),
            'bmdc_number' => 'BMDC-'.uniqid(),
            'bmdc_verified_at' => now()->subMonth(),
            'photo_path' => 'doctors/photo.jpg',
            'degrees_line_bn' => 'এমবিবিএস, এফসিপিএস',
            'experience_years' => 12,
            'current_position_bn' => 'সার্জিক্যাল অনকোলজিস্ট',
            'gender' => 'female',
            'status' => DoctorStatus::Published,
            'doctor_approved_at' => now()->subMonth(),
        ]);

        $cancer = CancerType::create([
            'name_bn' => 'স্তন',
            'name_en' => 'Breast Cancer',
            'slug' => 'breast-cancer',
            'icon' => 'ribbon',
            'color_key' => 'pink',
            'short_description_bn' => 'স্তনের কোষে অস্বাভাবিক বৃদ্ধি',
            'is_common' => true,
            'doctor_count_cache' => 0,
            'hospital_count_cache' => 0,
            'guide_published' => true,
            'sort_order' => 1,
        ]);

        $guide = Guide::create([
            'cancer_type_id' => $cancer->id,
            'title_bn' => 'স্তন ক্যান্সার — বিস্তারিত গাইড',
            'intro_bn' => 'স্তন ক্যান্সার চিকিৎসা ও রিপোর্টের সহজ বাংলা রূপরেখা।',
            'meta_title' => 'স্তন ক্যান্সার গাইড',
            'meta_description' => 'স্তন ক্যান্সারের লক্ষণ, স্টেজ, বায়োপসি ও চিকিৎসা।',
            'reviewed_by_doctor_id' => $doctor->id,
            'status' => GuideStatus::Published,
            'read_minutes' => 6,
            'published_at' => now(),
            'last_updated_at' => now(),
        ]);

        $video = GuideVideo::create([
            'guide_id' => $guide->id,
            'video_url' => 'https://youtube.com/watch?v=demo',
            'platform' => 'youtube',
            'title_bn' => 'স্তন ক্যান্সার — যা সবার আগে জানা দরকার',
            'description_bn' => 'লক্ষণ কী ও কখন ডাক্তার দেখাবেন।',
            'duration_seconds' => 138,
            'doctor_id' => $doctor->id,
            'sort_order' => 1,
        ]);

        $term = GuideTerm::create([
            'guide_id' => $guide->id,
            'code' => 'HER2 Positive',
            'slug' => 'her2-positive',
            'hint_bn' => 'টার্গেটেড ওষুধ লাগবে কিনা',
            'plain_explanation_bn' => 'HER2 একটি প্রোটিন যা কোষে বেশি থাকলে দ্রুত বৃদ্ধি পায়।',
            'why_matters_bn' => 'HER2 পজিটিভ হলে ট্রাস্টুজুমাব ওষুধ কার্যকর হয়।',
            'sort_order' => 1,
        ]);

        $stage1 = GuideStage::create([
            'guide_id' => $guide->id,
            'stage' => 'স্টেজ ১',
            'title_bn' => 'টিউমার ছোট, ছড়ায়নি',
            'description_bn' => 'সবচেয়ে আশাব্যঞ্জক পর্যায়।',
            'typical_treatment_bn' => 'অপারেশন + রেডিওথেরাপি',
            'duration_bn' => '৪–৬ মাস',
            'cost_min' => 120000,
            'cost_max' => 220000,
            'sort_order' => 1,
        ]);

        $step1 = GuideStep::create([
            'guide_id' => $guide->id,
            'step_no' => 1,
            'title_bn' => 'অনকোলজিস্ট দেখান',
            'description_bn' => 'ক্যান্সার বিশেষজ্ঞের পরামর্শ নিন।',
            'when_label_bn' => 'যত দ্রুত সম্ভব',
            'urgency' => 'urgent',
            'items' => ['বায়োপসি রিপোর্ট সাথে নিন', 'প্রশ্ন আগে লিখে রাখুন'],
            'sort_order' => 1,
        ]);

        $myth1 = GuideMyth::create([
            'guide_id' => $guide->id,
            'myth_bn' => 'অপারেশন করলে ক্যান্সার ছড়িয়ে পড়ে',
            'truth_bn' => 'এটি সম্পূর্ণ ভিত্তিহীন। অপারেশনই ক্যান্সার সারানোর প্রধান উপায়।',
            'sort_order' => 1,
        ]);

        $faq1 = GuideFaq::create([
            'guide_id' => $guide->id,
            'question_bn' => 'কেমোতে কি চুল পড়বেই?',
            'answer_bn' => 'সব কেমোতে চুল পড়ে না, চিকিৎসার পর আবার চুল গজায়।',
            'sort_order' => 1,
        ]);

        return [$cancer, $guide, $doctor, $video, $term, $stage1, $step1, $myth1, $faq1];
    }

    public function test_guide_detail_renders_all_sections(): void
    {
        [$cancer, $guide, $doctor] = $this->createPublishedGuide();

        $response = $this->get(route('guides.show', $cancer->slug));

        $response->assertOk();
        // Title & Header
        $response->assertSee('স্তন ক্যান্সার');
        $response->assertSee('Breast Cancer');
        $response->assertSee('ডা. সাদিয়া রহমান');
        $response->assertSee('6 মিনিট');
        $response->assertSee('এটি চিকিৎসা পরামর্শ নয়');

        // Video
        $response->assertSee('ডাক্তার নিজে বলছেন');
        $response->assertSee('স্তন ক্যান্সার — যা সবার আগে জানা দরকার');

        // Report Decoder
        $response->assertSee('আপনার রিপোর্টে এই শব্দগুলো আছে?');
        $response->assertSee('HER2 Positive');
        $response->assertSee('টার্গেটেড ওষুধ লাগবে কিনা');
        $response->assertSee('HER2 একটি প্রোটিন');
        $response->assertSee('ট্রাস্টুজুমাব ওষুধ কার্যকর হয়');

        // Stage Selector
        $response->assertSee('আপনার স্টেজে কী চিকিৎসা হয়');
        $response->assertSee('স্টেজ ১');
        $response->assertSee('অপারেশন + রেডিওথেরাপি');

        // Next steps
        $response->assertSee('পরবর্তী ধাপগুলো');
        $response->assertSee('অনকোলজিস্ট দেখান');
        $response->assertSee('যত দ্রুত সম্ভব');

        // Myths vs Truth
        $response->assertSee('ভুল ধারণা');
        $response->assertSee('অপারেশন করলে ক্যান্সার ছড়িয়ে পড়ে');
        $response->assertSee('অপারেশনই ক্যান্সার সারানোর প্রধান উপায়');

        // FAQs
        $response->assertSee('কেমোতে কি চুল পড়বেই?');
        $response->assertSee('সব কেমোতে চুল পড়ে না');

        // Sidebar & Helpline
        $response->assertSee('এই পাতায় রয়েছে');
        $response->assertSee('০৯৬১১-৭৭৭৮৮৮');
    }

    public function test_unpublished_or_draft_guide_returns_404(): void
    {
        $cancer = CancerType::create([
            'name_bn' => 'ফুসফুস',
            'name_en' => 'Lung Cancer',
            'slug' => 'lung-cancer',
            'icon' => 'lungs',
            'color_key' => 'sky',
            'short_description_bn' => 'ফুসফুস ক্যান্সার',
            'is_common' => true,
            'doctor_count_cache' => 0,
            'hospital_count_cache' => 0,
            'guide_published' => false,
            'sort_order' => 2,
        ]);

        Guide::create([
            'cancer_type_id' => $cancer->id,
            'title_bn' => 'খসড়া ফুসফুস গাইড',
            'intro_bn' => 'খসড়া ভূমিকা',
            'reviewed_by_doctor_id' => null,
            'status' => GuideStatus::Draft,
            'read_minutes' => 5,
        ]);

        $response = $this->get(route('guides.show', $cancer->slug));

        $response->assertNotFound();
    }
}
