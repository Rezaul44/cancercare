<?php

namespace Tests\Feature;

use App\Enums\DoctorStatus;
use App\Enums\GuideStatus;
use App\Models\CancerType;
use App\Models\Doctor;
use App\Models\Guide;
use App\Models\GuideTerm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuideIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_guide_index_renders_published_guides_and_coming_soon_card(): void
    {
        $doctor = Doctor::create([
            'name_bn' => 'ডা. সাদিয়া রহমান',
            'name_en' => 'Dr. Sadia Rahman',
            'slug' => 'dr-sadia-rahman-'.uniqid(),
            'bmdc_number' => 'BMDC-'.uniqid(),
            'bmdc_verified_at' => now()->subMonth(),
            'photo_path' => 'doctors/photo.jpg',
            'degrees_line_bn' => 'এমবিবিএস, এফসিপিএস',
            'experience_years' => 10,
            'current_position_bn' => 'সহযোগী অধ্যাপক',
            'gender' => 'female',
            'status' => DoctorStatus::Published,
            'doctor_approved_at' => now()->subMonth(),
        ]);

        $publishedCancer = CancerType::create([
            'name_bn' => 'স্তন',
            'name_en' => 'Breast Cancer',
            'slug' => 'breast-cancer',
            'icon' => 'ribbon',
            'color_key' => 'pink',
            'short_description_bn' => 'স্তনের কোষে অস্বাভাবিক বৃদ্ধি',
            'is_common' => true,
            'gender_bias' => 'female',
            'doctor_count_cache' => 0,
            'hospital_count_cache' => 0,
            'guide_published' => true,
            'sort_order' => 1,
        ]);

        $guide = Guide::create([
            'cancer_type_id' => $publishedCancer->id,
            'title_bn' => 'স্তন ক্যান্সার গাইড',
            'intro_bn' => 'স্তন ক্যান্সার চিকিৎসার সম্পূর্ণ বাংলা গাইড।',
            'reviewed_by_doctor_id' => $doctor->id,
            'status' => GuideStatus::Published,
            'read_minutes' => 6,
            'published_at' => now(),
        ]);

        GuideTerm::create([
            'guide_id' => $guide->id,
            'code' => 'HER2 Positive',
            'hint_bn' => 'প্রোটিন রিসেপ্টর',
            'plain_explanation_bn' => 'HER2 পজিটিভ মানে কোষে দ্রুত বৃদ্ধির সংকেত পাওয়া গেছে।',
            'sort_order' => 1,
        ]);

        // Unpublished cancer type
        CancerType::create([
            'name_bn' => 'ফুসফুস',
            'name_en' => 'Lung Cancer',
            'slug' => 'lung-cancer',
            'icon' => 'lungs',
            'color_key' => 'sky',
            'short_description_bn' => 'ফুসফুসের ক্যান্সার',
            'is_common' => true,
            'doctor_count_cache' => 0,
            'hospital_count_cache' => 0,
            'guide_published' => false,
            'sort_order' => 2,
        ]);

        $response = $this->get(route('guides.index'));

        $response->assertOk();
        $response->assertSee('ক্যান্সার সম্পর্কে জানুন');
        $response->assertSee('স্তন ক্যান্সার');
        $response->assertSee('ডা. সাদিয়া রহমান');
        $response->assertSee('6 মিনিট');
        $response->assertSee('1টি শব্দ');
        $response->assertSee('রিপোর্ট ডিকোডার');
        $response->assertSee('খরচের হিসাব');
        $response->assertSee('ডাক্তার ডিরেক্টরি');
        $response->assertSee('আরও 1টি ক্যান্সার গাইড আসছে');
        $response->assertSee('০৯৬১১-৭৭৭৮৮৮');
    }

    public function test_draft_guide_is_excluded_from_guide_index(): void
    {
        $cancer = CancerType::create([
            'name_bn' => 'রক্ত',
            'name_en' => 'Blood Cancer',
            'slug' => 'blood-cancer',
            'icon' => 'droplet',
            'color_key' => 'red',
            'short_description_bn' => 'রক্তের ক্যান্সার',
            'is_common' => true,
            'doctor_count_cache' => 0,
            'hospital_count_cache' => 0,
            'guide_published' => false,
            'sort_order' => 1,
        ]);

        Guide::create([
            'cancer_type_id' => $cancer->id,
            'title_bn' => 'রক্তের ক্যান্সার গাইড',
            'intro_bn' => 'খসড়া ভূমিকা',
            'reviewed_by_doctor_id' => null,
            'status' => GuideStatus::Draft,
            'read_minutes' => 5,
        ]);

        $response = $this->get(route('guides.index'));

        $response->assertOk();
        $response->assertDontSee('রক্ত ক্যান্সার');
        $response->assertSee('আরও 1টি ক্যান্সার গাইড আসছে');
    }

    public function test_search_by_cancer_name_and_report_term(): void
    {
        $doctor = Doctor::create([
            'name_bn' => 'ডা. আহমেদ',
            'name_en' => 'Dr. Ahmed',
            'slug' => 'dr-ahmed-'.uniqid(),
            'bmdc_number' => 'BMDC-'.uniqid(),
            'bmdc_verified_at' => now()->subMonth(),
            'photo_path' => 'doctors/photo.jpg',
            'degrees_line_bn' => 'এমবিবিএস',
            'experience_years' => 10,
            'current_position_bn' => 'অধ্যাপক',
            'gender' => 'male',
            'status' => DoctorStatus::Published,
            'doctor_approved_at' => now()->subMonth(),
        ]);

        $cancer = CancerType::create([
            'name_bn' => 'স্তন',
            'name_en' => 'Breast Cancer',
            'slug' => 'breast-cancer',
            'icon' => 'ribbon',
            'color_key' => 'pink',
            'short_description_bn' => 'স্তনের ক্যান্সার',
            'is_common' => true,
            'doctor_count_cache' => 0,
            'hospital_count_cache' => 0,
            'guide_published' => true,
            'sort_order' => 1,
        ]);

        $guide = Guide::create([
            'cancer_type_id' => $cancer->id,
            'title_bn' => 'স্তন ক্যান্সার নির্দেশিকা',
            'intro_bn' => 'স্তন গাইড ভূমিকা',
            'reviewed_by_doctor_id' => $doctor->id,
            'status' => GuideStatus::Published,
            'read_minutes' => 4,
            'published_at' => now(),
        ]);

        GuideTerm::create([
            'guide_id' => $guide->id,
            'code' => 'ER Positive',
            'hint_bn' => 'ইস্ট্রোজেন রিসেপ্টর',
            'plain_explanation_bn' => 'ER পজিটিভ মানে ইস্ট্রোজেনের প্রতি সংবেদনশীল।',
            'search_keywords' => 'Estrogen receptor positive',
            'sort_order' => 1,
        ]);

        // Search by cancer name
        $res1 = $this->get(route('guides.index', ['q' => 'স্তন']));
        $res1->assertOk();
        $res1->assertSee('স্তন ক্যান্সার');

        // Search by medical report term
        $res2 = $this->get(route('guides.index', ['q' => 'ER Positive']));
        $res2->assertOk();
        $res2->assertSee('ER Positive');
        $res2->assertSee('ইস্ট্রোজেন রিসেপ্টর');
        $res2->assertSee('স্তন ক্যান্সার');
    }
}
