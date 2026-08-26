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
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GuideDomainTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_guide_tables_exist(): void
    {
        $tables = [
            'guides',
            'guide_videos',
            'guide_terms',
            'guide_stages',
            'guide_steps',
            'guide_myths',
            'guide_faqs',
        ];

        foreach ($tables as $table) {
            $this->assertTrue(Schema::hasTable($table), "Table {$table} should exist.");
        }
    }

    public function test_guide_cannot_be_published_without_reviewed_by_doctor_id(): void
    {
        $cancer = $this->createCancerType();

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('reviewed_by_doctor_id');

        Guide::create([
            'cancer_type_id' => $cancer->id,
            'title_bn' => 'স্তন ক্যান্সার সম্পূর্ণ গাইড',
            'intro_bn' => 'স্তন ক্যান্সারের বিস্তারিত তথ্য।',
            'status' => GuideStatus::Published,
            'reviewed_by_doctor_id' => null,
        ]);
    }

    public function test_draft_or_in_review_guide_can_be_saved_without_doctor(): void
    {
        $cancer = $this->createCancerType();

        $draftGuide = Guide::create([
            'cancer_type_id' => $cancer->id,
            'title_bn' => 'স্তন ক্যান্সার খসড়া গাইড',
            'intro_bn' => 'স্তন ক্যান্সারের খসড়া তথ্য।',
            'status' => GuideStatus::Draft,
            'reviewed_by_doctor_id' => null,
        ]);

        $this->assertSame(GuideStatus::Draft, $draftGuide->status);
        $this->assertNull($draftGuide->reviewed_by_doctor_id);

        $draftGuide->update(['status' => GuideStatus::InReview]);
        $this->assertSame(GuideStatus::InReview, $draftGuide->refresh()->status);
    }

    public function test_guide_with_reviewed_by_doctor_publishes_successfully(): void
    {
        $cancer = $this->createCancerType();
        $doctor = $this->createDoctor();

        $guide = Guide::create([
            'cancer_type_id' => $cancer->id,
            'title_bn' => 'স্তন ক্যান্সার সম্পূর্ণ গাইড',
            'intro_bn' => 'স্তন ক্যান্সারের বিস্তারিত তথ্য।',
            'reviewed_by_doctor_id' => $doctor->id,
            'reviewed_at' => now()->toDateString(),
            'status' => GuideStatus::Published,
            'published_at' => now(),
        ]);

        $this->assertSame(GuideStatus::Published, $guide->status);
        $this->assertSame($doctor->id, $guide->reviewed_by_doctor_id);
    }

    public function test_published_scope_filters_only_published_guides(): void
    {
        $cancer1 = $this->createCancerType(['slug' => 'cancer-1-'.uniqid()]);
        $cancer2 = $this->createCancerType(['slug' => 'cancer-2-'.uniqid()]);
        $doctor = $this->createDoctor();

        $published = Guide::create([
            'cancer_type_id' => $cancer1->id,
            'title_bn' => 'প্রকাশিত গাইড',
            'intro_bn' => 'বিবরণ',
            'reviewed_by_doctor_id' => $doctor->id,
            'status' => GuideStatus::Published,
        ]);

        Guide::create([
            'cancer_type_id' => $cancer2->id,
            'title_bn' => 'খসড়া গাইড',
            'intro_bn' => 'বিবরণ',
            'status' => GuideStatus::Draft,
        ]);

        $results = Guide::published()->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->first()->is($published));
    }

    public function test_guide_has_many_relationships_and_casts(): void
    {
        $cancer = $this->createCancerType();
        $doctor = $this->createDoctor();

        $guide = Guide::create([
            'cancer_type_id' => $cancer->id,
            'title_bn' => 'স্তন ক্যান্সার গাইড',
            'intro_bn' => 'বিবরণ',
            'reviewed_by_doctor_id' => $doctor->id,
            'status' => GuideStatus::Published,
        ]);

        // GuideVideo
        $video = GuideVideo::create([
            'guide_id' => $guide->id,
            'video_url' => 'https://www.youtube.com/watch?v=sample',
            'platform' => 'youtube',
            'title_bn' => 'স্তন ক্যান্সার প্রাথমিক ধারণা',
            'duration_seconds' => 380,
            'doctor_id' => $doctor->id,
            'sort_order' => 1,
        ]);

        // GuideTerm (রিপোর্ট ডিকোডার)
        $term = GuideTerm::create([
            'guide_id' => $guide->id,
            'code' => 'HER2 Positive',
            'slug' => 'her2',
            'hint_bn' => 'টার্গেটেড ওষুধ লাগবে কিনা',
            'plain_explanation_bn' => 'HER2 হলো এক ধরণের প্রোটিন যা ক্যান্সার কোষের বৃদ্ধি দ্রুত করে।',
            'why_matters_bn' => 'HER2 পজিটিভ হলে ট্রাস্টুজুমাব বা টার্গেটেড থেরাপি কাজ করে।',
            'scale' => [
                ['label' => '0 (Negative)', 'desc' => 'টার্গেটেড ওষুধ দরকার নেই'],
                ['label' => '1+ (Low)', 'desc' => 'সীমিত প্রকাশ'],
                ['label' => '3+ (Positive)', 'desc' => 'টার্গেটেড ওষুধ কার্যকর'],
            ],
            'search_keywords' => 'HER2 neu, erbb2, targeted therapy',
            'sort_order' => 1,
        ]);

        // GuideStage
        $stage = GuideStage::create([
            'guide_id' => $guide->id,
            'stage' => '1',
            'title_bn' => 'স্টেজ ১ (প্রাথমিক ধাপ)',
            'description_bn' => 'টিউমারের আকার ২ সেমি-র নিচে এবং লিম্ফ নোডে ছড়ায়নি।',
            'typical_treatment_bn' => 'সার্জারি + রেডিওথেরাপি',
            'duration_bn' => '৩–৬ মাস',
            'cost_min' => 45000,
            'cost_max' => 180000,
            'severity_color' => 'teal',
            'sort_order' => 1,
        ]);

        // GuideStep
        $step = GuideStep::create([
            'guide_id' => $guide->id,
            'step_no' => 1,
            'title_bn' => 'বিশেষজ্ঞ ডাক্তার দেখান',
            'description_bn' => 'রিপোর্ট পাওয়ার সাথে সাথে সার্জিক্যাল অনকোলজিস্টের সাথে পরামর্শ করুন।',
            'when_label_bn' => '১–৩ দিনের মধ্যে',
            'urgency' => 'urgent',
            'items' => [
                'বায়োপসি রিপোর্ট ও আগের সব প্রেসক্রিপশন সাথে রাখুন',
                'পরিবারের একজন সদস্যকে সাথে নিন',
            ],
            'sort_order' => 1,
        ]);

        // GuideMyth
        $myth = GuideMyth::create([
            'guide_id' => $guide->id,
            'myth_bn' => 'বায়োপসি করলে ক্যান্সার ছড়িয়ে পড়ে।',
            'truth_bn' => 'বায়োপসি সম্পূর্ণ নিরাপদ ও নিশ্চিত রোগ নির্ণয়ের একমাত্র বৈজ্ঞানিক পদ্ধতি।',
            'sort_order' => 1,
        ]);

        // GuideFaq
        $faq = GuideFaq::create([
            'guide_id' => $guide->id,
            'question_bn' => 'স্তন ক্যান্সার কি বংশগত?',
            'answer_bn' => 'শতকরা ৫-১০ ভাগ ক্ষেত্রে BRCA1 বা BRCA2 জিনের মিউটেশনের কারণে বংশগত হতে পারে।',
            'sort_order' => 1,
        ]);

        $this->assertCount(1, $guide->videos);
        $this->assertCount(1, $guide->terms);
        $this->assertCount(1, $guide->stages);
        $this->assertCount(1, $guide->steps);
        $this->assertCount(1, $guide->myths);
        $this->assertCount(1, $guide->faqs);

        $this->assertIsArray($term->refresh()->scale);
        $this->assertCount(3, $term->scale);
        $this->assertIsArray($step->refresh()->items);
        $this->assertCount(2, $step->items);

        // Reverse relationships
        $this->assertTrue($cancer->guide()->first()->is($guide));
        $this->assertTrue($doctor->reviewedGuides()->first()->is($guide));
        $this->assertTrue($doctor->guideVideos()->first()->is($video));
    }

    private function createCancerType(array $overrides = []): CancerType
    {
        return CancerType::create(array_merge([
            'name_bn' => 'স্তন ক্যান্সার',
            'name_en' => 'Breast Cancer',
            'slug' => 'breast-cancer-'.uniqid(),
            'icon' => 'ribbon',
            'color_key' => 'pink',
            'short_description_bn' => 'স্তন ক্যান্সারের বিবরণ',
            'is_common' => true,
            'doctor_count_cache' => 0,
            'hospital_count_cache' => 0,
            'guide_published' => true,
            'sort_order' => 1,
        ], $overrides));
    }

    private function createDoctor(array $overrides = []): Doctor
    {
        return Doctor::create(array_merge([
            'name_bn' => 'ডা. তানিয়া আহমেদ',
            'name_en' => 'Dr. Tania Ahmed',
            'slug' => 'dr-tania-ahmed-'.uniqid(),
            'bmdc_number' => 'BMDC-'.uniqid(),
            'bmdc_verified_at' => now()->subMonth(),
            'photo_path' => 'doctors/photo.jpg',
            'degrees_line_bn' => 'এমবিবিএস, এফসিপিএস',
            'experience_years' => 12,
            'current_position_bn' => 'সহযোগী অধ্যাপক',
            'gender' => 'female',
            'status' => DoctorStatus::Published,
            'doctor_approved_at' => now()->subMonth(),
        ], $overrides));
    }
}
