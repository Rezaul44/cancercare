<?php

namespace Tests\Feature;

use App\Enums\DoctorStatus;
use App\Enums\GuideStatus;
use App\Models\CancerType;
use App\Models\Chamber;
use App\Models\District;
use App\Models\Doctor;
use App\Models\DoctorType;
use App\Models\Guide;
use App\Models\GuideFaq;
use App\Models\GuideTerm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    private function createPublishedDoctor(string $nameBn = 'ডা. সাদিয়া রহমান', string $slug = 'dr-sadia-rahman'): Doctor
    {
        $divisionId = DB::table('divisions')->insertGetId([
            'name_bn' => 'ঢাকা',
            'name_en' => 'Dhaka',
            'slug' => 'dhaka-'.uniqid(),
        ]);

        $district = District::create([
            'division_id' => $divisionId,
            'name_bn' => 'ঢাকা',
            'name_en' => 'Dhaka',
            'slug' => 'dhaka-'.uniqid(),
            'distance_tier' => 'local',
            'has_cancer_center' => true,
        ]);

        $doctor = Doctor::create([
            'name_bn' => $nameBn,
            'name_en' => 'Dr. '.ucwords(str_replace(['-', 'dr '], ['', ''], $slug)),
            'slug' => $slug,
            'bmdc_number' => 'BMDC-'.rand(10000, 99999),
            'bmdc_verified_at' => now(),
            'photo_path' => 'doctors/photo.jpg',
            'degrees_line_bn' => 'এমবিবিএস, এফসিপিএস',
            'experience_years' => 15,
            'current_position_bn' => 'সিনিয়র কনসালট্যান্ট',
            'gender' => 'female',
            'status' => DoctorStatus::Published,
            'doctor_approved_at' => now(),
        ]);

        $doctorType = DoctorType::firstOrCreate(
            ['key' => 'surgical_oncologist'],
            ['label_bn' => 'সার্জিক্যাল অনকোলজিস্ট', 'label_en' => 'Surgical Oncologist', 'order' => 1]
        );
        $doctor->doctorTypes()->sync([$doctorType->id]);

        Chamber::create([
            'doctor_id' => $doctor->id,
            'district_id' => $district->id,
            'name_bn' => 'স্কয়ার হাসপাতাল',
            'address_bn' => 'পান্থপথ, ঢাকা',
            'fee' => 1200,
            'days_bn' => 'রবি-বৃহস্পতি',
            'time_from' => '17:00:00',
            'time_to' => '20:00:00',
            'type' => 'private',
            'is_active' => true,
        ]);

        return $doctor;
    }

    private function createPublishedGuide(string $cancerSlug = 'breast-cancer'): array
    {
        $doctor = $this->createPublishedDoctor('ডা. তানভীর আহমেদ', 'dr-tanvir-ahmed-'.uniqid());

        $cancerType = CancerType::create([
            'name_bn' => 'স্তন',
            'name_en' => 'Breast Cancer',
            'slug' => $cancerSlug,
            'icon' => 'ribbon',
            'color_key' => 'pink',
            'short_description_bn' => 'স্তন ক্যান্সার সম্পর্কে বিস্তারিত তথ্য ও গাইড।',
            'is_common' => true,
            'guide_published' => true,
            'sort_order' => 1,
        ]);

        $guide = Guide::create([
            'cancer_type_id' => $cancerType->id,
            'reviewed_by_doctor_id' => $doctor->id,
            'status' => GuideStatus::Published,
            'title_bn' => 'স্তন ক্যান্সার — লক্ষণ, রিপোর্ট ও চিকিৎসা গাইড',
            'intro_bn' => 'স্তন ক্যান্সার সম্পর্কে বিস্তারিত তথ্য ও রিপোর্ট বোঝার গাইড।',
            'meta_description' => 'স্তন ক্যান্সারের লক্ষণ, বায়োপসি রিপোর্ট ও চিকিৎসার সম্পূর্ণ গাইড।',
            'read_minutes' => 6,
            'reviewed_at' => now(),
            'published_at' => now(),
            'last_updated_at' => now(),
        ]);

        GuideFaq::create([
            'guide_id' => $guide->id,
            'question_bn' => 'স্তন ক্যান্সারের প্রাথমিক লক্ষণ কী?',
            'answer_bn' => 'স্তনে ব্যথাহীন চাকা বা ফোলাভাব অন্যতম প্রধান লক্ষণ।',
            'order' => 1,
        ]);

        GuideTerm::create([
            'guide_id' => $guide->id,
            'code' => 'HER2 Positive',
            'slug' => 'her2-positive',
            'hint_bn' => 'রিসেপ্টর প্রোটিন',
            'plain_explanation_bn' => 'কোষের বৃদ্ধির জন্য দায়ী বিশেষ রিসেপ্টর প্রোটিন।',
            'why_matters_bn' => 'টার্গেটেড থেরাপির সিদ্ধান্ত নিতে সাহায্য করে।',
            'order' => 1,
        ]);

        return [$guide, $cancerType, $doctor];
    }

    public function test_sitemap_xml_renders_valid_xml_with_published_guides_and_doctors(): void
    {
        [$guide, $cancerType, $doctor] = $this->createPublishedGuide('breast-cancer');

        // Create a draft guide and draft doctor
        $draftCancer = CancerType::create([
            'name_bn' => 'অপ্রকাশিত ক্যান্সার',
            'name_en' => 'Draft Cancer',
            'slug' => 'draft-cancer-'.uniqid(),
            'icon' => 'ribbon',
            'color_key' => 'slate',
            'short_description_bn' => 'খসড়া ক্যান্সারের বিবরণ',
            'is_common' => false,
            'guide_published' => false,
            'sort_order' => 99,
        ]);

        Guide::create([
            'cancer_type_id' => $draftCancer->id,
            'status' => GuideStatus::Draft,
            'title_bn' => 'খসড়া গাইড',
            'intro_bn' => 'খসড়া পরিচয়',
            'read_minutes' => 5,
        ]);

        Doctor::create([
            'name_bn' => 'ডা. অপ্রকাশিত রহমান',
            'name_en' => 'Dr. Unpublished Rahman',
            'slug' => 'dr-unpublished-doctor-'.uniqid(),
            'bmdc_number' => 'BMDC-'.rand(10000, 99999),
            'photo_path' => 'doctors/photo.jpg',
            'degrees_line_bn' => 'এমবিবিএস',
            'current_position_bn' => 'কনসালট্যান্ট',
            'gender' => 'male',
            'status' => DoctorStatus::Draft,
            'experience_years' => 10,
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml');

        $content = $response->getContent();

        // Must contain XML declaration and urlset tag
        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $content);
        $this->assertStringContainsString('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', $content);

        // Must contain core static pages
        $this->assertStringContainsString(url('/'), $content);
        $this->assertStringContainsString(route('doctors.index'), $content);
        $this->assertStringContainsString(route('guides.index'), $content);
        $this->assertStringContainsString(route('doctors.apply'), $content);

        // Must contain published guide and doctor
        $this->assertStringContainsString(route('guides.show', 'breast-cancer'), $content);
        $this->assertStringContainsString(route('doctors.show', $doctor->slug), $content);

        // Must NOT contain draft guide or draft doctor
        $this->assertStringNotContainsString('draft-cancer', $content);
        $this->assertStringNotContainsString('dr-unpublished-doctor', $content);
    }

    public function test_sitemap_generate_artisan_command_writes_file(): void
    {
        $this->createPublishedGuide('lung-cancer-'.uniqid());

        $customPath = storage_path('framework/testing/test_sitemap.xml');
        File::ensureDirectoryExists(dirname($customPath));

        $exitCode = Artisan::call('sitemap:generate', ['--path' => $customPath]);

        $this->assertEquals(0, $exitCode);
        $this->assertFileExists($customPath);

        $content = File::get($customPath);
        $this->assertStringContainsString('<urlset', $content);
        $this->assertStringContainsString('lung-cancer', $content);

        // Clean up
        File::delete($customPath);
    }

    public function test_robots_txt_contains_disallow_rules_and_sitemap_url(): void
    {
        $robotsPath = public_path('robots.txt');
        $this->assertFileExists($robotsPath);

        $robotsContent = File::get($robotsPath);

        $this->assertStringContainsString('Disallow: /admin', $robotsContent);
        $this->assertStringContainsString('Disallow: /field', $robotsContent);
        $this->assertStringContainsString('Disallow: /ajax/', $robotsContent);
        $this->assertStringContainsString('Disallow: /private-document', $robotsContent);
        $this->assertStringContainsString('Sitemap:', $robotsContent);
    }

    public function test_guide_detail_renders_medical_web_page_faq_and_term_schemas(): void
    {
        [$guide, $cancerType, $doctor] = $this->createPublishedGuide('breast-cancer');

        $response = $this->get('/guide/breast-cancer');

        $response->assertStatus(200);

        // OpenGraph & Meta
        $response->assertSee('<meta property="og:type" content="article">', false);
        $response->assertSee('<meta property="og:locale" content="bn_BD">', false);
        $response->assertSee('<link rel="canonical" href="'.route('guides.show', 'breast-cancer').'">', false);

        // Schema.org MedicalWebPage JSON-LD
        $response->assertSee('"@type": "MedicalWebPage"', false);
        $response->assertSee('"@type": "MedicalCondition"', false);
        $response->assertSee('"name": "স্তন ক্যান্সার"', false);
        $response->assertSee('"@type": "Physician"', false);
        $response->assertSee('"name": "'.$doctor->name_bn.'"', false);

        // Schema.org FAQPage
        $response->assertSee('"@type": "FAQPage"', false);
        $response->assertSee('স্তন ক্যান্সারের প্রাথমিক লক্ষণ কী?', false);

        // Schema.org DefinedTermSet
        $response->assertSee('"@type": "DefinedTermSet"', false);
        $response->assertSee('"termCode": "HER2 Positive"', false);
    }

    public function test_doctor_profile_renders_physician_schema_and_meta(): void
    {
        $doctor = $this->createPublishedDoctor('ডা. ফারহানা হক', 'dr-farhana-haque');

        $response = $this->get('/doctors/dr-farhana-haque');

        $response->assertStatus(200);

        // OpenGraph & Meta
        $response->assertSee('<meta property="og:type" content="profile">', false);
        $response->assertSee('<link rel="canonical" href="'.route('doctors.show', 'dr-farhana-haque').'">', false);

        // Schema.org Physician JSON-LD
        $response->assertSee('"@type": "Physician"', false);
        $response->assertSee('"name": "ডা. ফারহানা হক"', false);
        $response->assertSee('"@type": "MedicalClinic"', false);
        $response->assertSee('"name": "স্কয়ার হাসপাতাল"', false);
    }

    public function test_homepage_and_guide_index_have_meta_and_organization_schemas(): void
    {
        $responseHome = $this->get('/');
        $responseHome->assertStatus(200);
        $responseHome->assertSee('<meta property="og:site_name" content="CancerCare Bangladesh">', false);
        $responseHome->assertSee('"@type": "MedicalOrganization"', false);
        $responseHome->assertSee('"@type": "WebSite"', false);

        $responseGuideIndex = $this->get('/guide');
        $responseGuideIndex->assertStatus(200);
        $responseGuideIndex->assertSee('<meta property="og:site_name" content="CancerCare Bangladesh">', false);
        $responseGuideIndex->assertSee('ক্যান্সার গাইড — CancerCare Bangladesh', false);
    }
}
