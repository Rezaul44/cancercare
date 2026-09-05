<?php

namespace Tests\Feature;

use App\Enums\DoctorStatus;
use App\Enums\GuideStatus;
use App\Enums\HospitalStatus;
use App\Enums\HospitalType;
use App\Enums\PatientCaseStatus;
use App\Models\CancerType;
use App\Models\Doctor;
use App\Models\District;
use App\Models\Guide;
use App\Models\Hospital;
use App\Models\PatientCase;
use App\Models\SearchLog;
use App\Services\SearchService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Phase 8.1 — SearchService: ৫ ধরনের কন্টেন্ট, দৃশ্যমানতা স্কোপ, ওজন-ভিত্তিক র‍্যাংকিং,
 * এবং শূন্য-ফলাফলের search_logs এন্ট্রি যাচাই করে।
 *
 * ⚠️ ইচ্ছাকৃতভাবে RefreshDatabase ব্যবহার করা হয়নি — InnoDB-এর FULLTEXT ইনডেক্স একই
 * ট্রানজেকশনে (কমিট না হওয়া পর্যন্ত) নিজের সদ্য-ইনসার্ট করা রো MATCH...AGAINST-এ দেখতে
 * পায় না, আর RefreshDatabase প্রতিটি টেস্টকে ঠিক এমন একটি আনকমিটেড ট্রানজেকশনে মুড়ে
 * রাখে — ফলে এখানে প্রতিটি সার্চ শূন্য ফলাফল দিত। তাই এখানে ম্যানুয়ালি watermark
 * (setUp-এ প্রতিটি টেবিলের সর্বোচ্চ id) রেখে tearDown-এ শুধু এই টেস্টে তৈরি রো-গুলো
 * মুছে ফেলা হয় — সাধারণ insert/commit আচরণ বজায় থাকে, FULLTEXT ঠিকভাবে কাজ করে।
 */
class SearchTest extends TestCase
{
    /** @var array<int, string> */
    private const CLEANUP_TABLES_IN_ORDER = [
        'guide_terms', 'guides', 'patient_cases', 'hospitals', 'doctors',
        'search_logs', 'districts', 'divisions', 'cancer_types',
    ];

    /** @var array<string, int> */
    private array $watermarks = [];

    protected function setUp(): void
    {
        parent::setUp();

        foreach (self::CLEANUP_TABLES_IN_ORDER as $table) {
            $this->watermarks[$table] = (int) (DB::table($table)->max('id') ?? 0);
        }
    }

    protected function tearDown(): void
    {
        foreach (self::CLEANUP_TABLES_IN_ORDER as $table) {
            DB::table($table)->where('id', '>', $this->watermarks[$table])->delete();
        }

        parent::tearDown();
    }

    public function test_search_finds_published_records_across_all_types_and_excludes_unpublished(): void
    {
        $district = $this->makeDistrict();
        $cancerType = $this->makeCancerType();
        $doctor = $this->makeDoctor(['name_bn' => 'ডা. বিরলতম নাম টেস্টার']);
        $draftDoctor = $this->makeDoctor(['name_bn' => 'ডা. বিরলতম নাম টেস্টার খসড়া', 'status' => DoctorStatus::Draft, 'bmdc_number' => 'BMDC-'.uniqid()]);
        $hospital = $this->makeHospital($district, ['name_bn' => 'বিরলতম নাম হাসপাতাল']);
        $guide = $this->makeGuide($cancerType, $doctor, ['title_bn' => 'বিরলতম নাম গাইড শিরোনাম']);
        $case = $this->makePatientCase($cancerType, $district, ['display_name_bn' => 'বিরলতম নাম রোগী']);

        $service = app(SearchService::class);
        $results = $service->search('বিরলতম নাম');

        $this->assertCount(1, $results['doctors']);
        $this->assertSame($doctor->name_bn, $results['doctors'][0]['title']);

        $this->assertCount(1, $results['hospitals']);
        $this->assertSame($hospital->name_bn, $results['hospitals'][0]['title']);

        $this->assertGreaterThanOrEqual(1, count($results['guides']));
        $this->assertTrue(collect($results['guides'])->contains('title', $guide->title_bn));

        $this->assertCount(1, $results['patient_cases']);
        $this->assertSame($case->display_name_bn, $results['patient_cases'][0]['title']);

        // Draft doctor never appears, even though the name matches
        $this->assertFalse(collect($results['doctors'])->contains('title', $draftDoctor->name_bn));
    }

    public function test_patient_case_results_never_expose_real_name(): void
    {
        $district = $this->makeDistrict();
        $cancerType = $this->makeCancerType();
        $case = $this->makePatientCase($cancerType, $district, [
            'real_name' => 'সত্যিকারের-গোপনীয়-নাম-খুঁজলে-পাওয়া-যাবে-না',
            'display_name_bn' => 'ছদ্মনাম রোগীর গল্প',
            'story_bn' => 'একটি বিশেষ পরীক্ষামূলক গল্প যা সহজে খুঁজে পাওয়া যাবে।',
        ]);

        $results = app(SearchService::class)->search('বিশেষ পরীক্ষামূলক গল্প');

        $this->assertNotEmpty($results['patient_cases']);
        $payload = json_encode($results['patient_cases'], JSON_UNESCAPED_UNICODE);
        $this->assertStringNotContainsString('সত্যিকারের-গোপনীয়-নাম', $payload);
        $this->assertStringContainsString('ছদ্মনাম রোগীর গল্প', $payload);
    }

    public function test_zero_result_query_writes_one_search_log_row_per_call(): void
    {
        $service = app(SearchService::class);

        $service->search('kjqwlekjqwlkejqwoiuzxcvasdf');
        $this->assertSame(1, $this->newRowCount('search_logs'));

        $service->suggest('kjqwlekjqwlkejqwoiuzxcvasdf');
        $this->assertSame(2, $this->newRowCount('search_logs'));

        $log = SearchLog::orderByDesc('id')->first();
        $this->assertSame(0, $log->results_count);
        $this->assertNotEmpty($log->session_hash);
    }

    public function test_non_zero_result_query_does_not_write_a_search_log(): void
    {
        $this->makeDoctor(['name_bn' => 'ডা. অতিবিরল নাম শনাক্তকারী']);

        app(SearchService::class)->search('অতিবিরল নাম শনাক্তকারী');

        $this->assertSame(0, $this->newRowCount('search_logs'));
    }

    private function newRowCount(string $table): int
    {
        return (int) DB::table($table)->where('id', '>', $this->watermarks[$table])->count();
    }

    public function test_blended_ranking_weighs_guide_terms_above_patient_cases(): void
    {
        // guide_terms ওজন ১.০, patient_cases ওজন ০.৫ — সমান relevance-এ guide_term আগে আসা উচিত।
        $district = $this->makeDistrict();
        $cancerType = $this->makeCancerType();
        $doctor = $this->makeDoctor();
        $guide = $this->makeGuide($cancerType, $doctor);

        \App\Models\GuideTerm::create([
            'guide_id' => $guide->id,
            'code' => 'অভিন্নশব্দ-টার্ম',
            'hint_bn' => 'অভিন্নশব্দ পরিভাষা',
            'plain_explanation_bn' => 'অভিন্নশব্দ ব্যাখ্যা পরীক্ষা অভিন্নশব্দ',
            'why_matters_bn' => 'কারণ',
            'search_keywords' => 'অভিন্নশব্দ',
        ]);

        $this->makePatientCase($cancerType, $district, [
            'display_name_bn' => 'অভিন্নশব্দ রোগী',
            'story_bn' => 'অভিন্নশব্দ ব্যাখ্যা পরীক্ষা অভিন্নশব্দ',
        ]);

        $results = app(SearchService::class)->search('অভিন্নশব্দ');

        $topType = $results['all'][0]['type'] ?? null;
        $this->assertSame('guide_term', $topType);
    }

    public function test_ajax_suggest_endpoint_returns_grouped_json(): void
    {
        $this->makeDoctor(['name_bn' => 'ডা. এন্ডপয়েন্ট পরীক্ষা']);

        $response = $this->getJson(route('ajax.search.suggest', ['q' => 'এন্ডপয়েন্ট পরীক্ষা']));

        $response->assertOk();
        $response->assertJsonStructure(['doctors', 'hospitals', 'guides', 'patient_cases']);
        $this->assertCount(1, $response->json('doctors'));
    }

    private function makeDoctor(array $overrides = []): Doctor
    {
        return Doctor::create(array_merge([
            'name_bn' => 'ডা. সাদিয়া রহমান',
            'name_en' => 'Sadia Rahman',
            'slug' => 'doctor-'.uniqid(),
            'bmdc_number' => 'BMDC-'.uniqid(),
            'photo_path' => 'doctors/photo.jpg',
            'degrees_line_bn' => 'এমবিবিএস, এফসিপিএস',
            'experience_years' => 10,
            'current_position_bn' => 'সহকারী অধ্যাপক',
            'gender' => 'female',
            'status' => DoctorStatus::Published,
            'doctor_approved_at' => now(),
            'rotation_seed' => random_int(1, 1000),
        ], $overrides));
    }

    private function makeHospital(District $district, array $overrides = []): Hospital
    {
        return Hospital::create(array_merge([
            'name_bn' => 'পরীক্ষামূলক হাসপাতাল',
            'name_en' => 'Test Hospital',
            'slug' => 'hospital-'.uniqid(),
            'type' => HospitalType::Govt,
            'district_id' => $district->id,
            'address_bn' => 'ঢাকা',
            'phone' => '02-000000',
            'established_year' => 1990,
            'bed_count' => 100,
            'oncologist_count' => 5,
            'outdoor_fee' => 50,
            'emergency_24h' => true,
            'annual_patients' => '১০,০০০+',
            'description_bn' => 'হাসপাতাল পরিচিতি',
            'status' => HospitalStatus::Published,
            'last_verified_at' => now(),
        ], $overrides));
    }

    private function makeGuide(CancerType $cancerType, Doctor $doctor, array $overrides = []): Guide
    {
        return Guide::create(array_merge([
            'cancer_type_id' => $cancerType->id,
            'title_bn' => 'পরীক্ষামূলক গাইড শিরোনাম',
            'intro_bn' => 'গাইডের ভূমিকা টেক্সট',
            'reviewed_by_doctor_id' => $doctor->id,
            'reviewed_at' => now(),
            'read_minutes' => 5,
            'status' => GuideStatus::Published,
            'published_at' => now(),
        ], $overrides));
    }

    private function makePatientCase(CancerType $cancerType, District $district, array $overrides = []): PatientCase
    {
        return PatientCase::create(array_merge([
            'real_name' => 'প্রকৃত নাম',
            'display_name_bn' => 'ছদ্মনাম',
            'age' => 45,
            'gender' => 'female',
            'cancer_type_id' => $cancerType->id,
            'district_id' => $district->id,
            'story_bn' => 'রোগীর গল্প টেক্সট',
            'amount_needed' => 50000,
            'status' => PatientCaseStatus::Published,
        ], $overrides));
    }

    private function makeDistrict(): District
    {
        $divisionId = \Illuminate\Support\Facades\DB::table('divisions')->insertGetId([
            'name_bn' => 'ঢাকা',
            'name_en' => 'Dhaka',
            'slug' => 'dhaka',
        ]);

        return District::create([
            'division_id' => $divisionId,
            'name_bn' => 'ঢাকা',
            'name_en' => 'Dhaka',
            'slug' => 'dhaka-'.uniqid(),
            'distance_tier' => 'local',
            'has_cancer_center' => true,
        ]);
    }

    private function makeCancerType(): CancerType
    {
        return CancerType::create([
            'name_bn' => 'স্তন',
            'name_en' => 'Breast Cancer',
            'slug' => 'breast-cancer-'.uniqid(),
            'icon' => 'ribbon',
            'color_key' => 'pink',
            'short_description_bn' => 'বিবরণ',
            'is_common' => true,
            'doctor_count_cache' => 0,
            'hospital_count_cache' => 0,
            'guide_published' => false,
            'sort_order' => 1,
        ]);
    }
}
