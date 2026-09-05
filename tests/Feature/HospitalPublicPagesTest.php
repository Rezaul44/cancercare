<?php

namespace Tests\Feature;

use App\Enums\HospitalCapabilityStatus;
use App\Enums\HospitalFlagType;
use App\Enums\HospitalPracticalKey;
use App\Enums\HospitalPrepKey;
use App\Enums\HospitalStatus;
use App\Enums\HospitalType;
use App\Enums\HospitalWaitTimeSeverity;
use App\Models\Capability;
use App\Models\District;
use App\Models\Hospital;
use App\Models\HospitalCapability;
use App\Models\HospitalCost;
use App\Models\HospitalExperienceQuestion;
use App\Models\HospitalExperienceSummary;
use App\Models\HospitalPracticalInfo;
use App\Models\HospitalPrepInfo;
use App\Models\HospitalVideo;
use App\Models\HospitalWaitTime;
use Database\Seeders\CapabilitySeeder;
use Database\Seeders\DivisionDistrictSeeder;
use Database\Seeders\HospitalExperienceQuestionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HospitalPublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected District $dhakaDistrict;

    protected District $chittagongDistrict;

    protected function setUp(): void
    {
        parent::setUp();

        (new RolePermissionSeeder)->run();
        (new CapabilitySeeder)->run();
        (new HospitalExperienceQuestionSeeder)->run();
        (new DivisionDistrictSeeder)->run();

        $this->dhakaDistrict = District::where('slug', 'dhaka')->first();
        $this->chittagongDistrict = District::where('slug', 'chittagong')->first() ?? District::where('name_en', 'Chattogram')->first() ?? District::where('slug', 'gazipur')->first();
    }

    private function createHospital(array $attributes = []): Hospital
    {
        $hospital = Hospital::create(array_merge([
            'name_bn' => 'জাতীয় ক্যান্সার হাসপাতাল',
            'name_en' => 'National Cancer Hospital',
            'slug' => 'national-cancer-hospital-'.uniqid(),
            'type' => HospitalType::Govt,
            'district_id' => $this->dhakaDistrict->id,
            'address_bn' => 'মহাখালী, ঢাকা-১২১২',
            'phone' => '০২-৯৮৯৮৬০১',
            'established_year' => 1982,
            'bed_count' => 500,
            'oncologist_count' => 45,
            'outdoor_fee' => 30,
            'emergency_24h' => true,
            'annual_patients' => '২,৫০,০০০+',
            'description_bn' => 'বাংলাদেশের অন্যতম বৃহৎ বিশেষায়িত ক্যান্সার হাসপাতাল।',
            'status' => HospitalStatus::Published,
            'last_verified_at' => now(),
        ], $attributes));

        return $hospital;
    }

    public function test_hospital_directory_page_is_accessible_and_renders_published_hospitals(): void
    {
        $hospital = $this->createHospital([
            'name_bn' => 'ঢাকা ক্যান্সার হাসপাতাল',
            'slug' => 'dhaka-cancer-hospital',
        ]);

        $response = $this->get(route('hospitals.index'));

        $response->assertOk();
        $response->assertSee('কোথায় আসলে কী চিকিৎসা হয়');
        $response->assertSee('ঢাকা ক্যান্সার হাসপাতাল');
    }

    public function test_hospital_directory_filters_by_capability(): void
    {
        $hospitalWithRadio = $this->createHospital([
            'name_bn' => 'রেডিওথেরাপিসহ হাসপাতাল',
            'slug' => 'hospital-with-radio',
        ]);
        $hospitalWithoutRadio = $this->createHospital([
            'name_bn' => 'রেডিওথেরাপিবিহীন হাসপাতাল',
            'slug' => 'hospital-without-radio',
        ]);

        $radioCap = Capability::where('key', 'radiotherapy')->first();
        $this->assertNotNull($radioCap);

        HospitalCapability::create([
            'hospital_id' => $hospitalWithRadio->id,
            'capability_id' => $radioCap->id,
            'status' => HospitalCapabilityStatus::Available,
            'detail_bn' => '৩টি লিনিয়ার এক্সিলারেটর সচল',
        ]);

        HospitalCapability::create([
            'hospital_id' => $hospitalWithoutRadio->id,
            'capability_id' => $radioCap->id,
            'status' => HospitalCapabilityStatus::NotAvailable,
        ]);

        $response = $this->get(route('hospitals.index', ['capabilities' => ['radiotherapy']]));

        $response->assertOk();
        $response->assertSee('রেডিওথেরাপিসহ হাসপাতাল');
        $response->assertDontSee('রেডিওথেরাপিবিহীন হাসপাতাল');
    }

    public function test_hospital_directory_filters_by_hospital_type(): void
    {
        $govtHospital = $this->createHospital([
            'name_bn' => 'সরকারি ক্যান্সার ইনস্টিটিউট',
            'type' => HospitalType::Govt,
        ]);
        $privateHospital = $this->createHospital([
            'name_bn' => 'বেসরকারি স্পেশালাইজড ক্লিনিক',
            'type' => HospitalType::Private,
        ]);

        $response = $this->get(route('hospitals.index', ['types' => ['govt']]));

        $response->assertOk();
        $response->assertSee('সরকারি ক্যান্সার ইনস্টিটিউট');
        $response->assertDontSee('বেসরকারি স্পেশালাইজড ক্লিনিক');
    }

    public function test_hospital_directory_filters_by_facilities(): void
    {
        $emergencyHospital = $this->createHospital([
            'name_bn' => '২৪ঘণ্টা জরুরি হাসপাতাল',
            'emergency_24h' => true,
        ]);
        $dayHospital = $this->createHospital([
            'name_bn' => 'ডে-কেয়ার অনকোলজি সেন্টার',
            'emergency_24h' => false,
        ]);

        $response = $this->get(route('hospitals.index', ['facilities' => ['emergency_24h']]));

        $response->assertOk();
        $response->assertSee('২৪ঘণ্টা জরুরি হাসপাতাল');
        $response->assertDontSee('ডে-কেয়ার অনকোলজি সেন্টার');
    }

    public function test_hospital_directory_sorting(): void
    {
        $cheapHospital = $this->createHospital([
            'name_bn' => 'স্বল্প খরচের হাসপাতাল',
            'outdoor_fee' => 20,
        ]);
        $expensiveHospital = $this->createHospital([
            'name_bn' => 'প্রিমিয়াম হাসপাতাল',
            'outdoor_fee' => 1500,
        ]);

        $response = $this->get(route('hospitals.index', ['sort' => 'cost_low']));

        $response->assertOk();
        $response->assertSeeInOrder(['স্বল্প খরচের হাসপাতাল', 'প্রিমিয়াম হাসপাতাল']);
    }

    public function test_hospital_directory_ajax_partial_response(): void
    {
        $hospital = $this->createHospital([
            'name_bn' => 'এজ্যাক্স টেস্ট হাসপাতাল',
        ]);

        $response = $this->get(route('hospitals.index'), [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertOk();
        $response->assertSee('এজ্যাক্স টেস্ট হাসপাতাল');
        $response->assertDontSee('<!DOCTYPE html>');
    }

    public function test_hospital_card_shows_both_available_and_unavailable_capabilities(): void
    {
        $hospital = $this->createHospital();
        $radioCap = Capability::where('key', 'radiotherapy')->first();
        $bmtCap = Capability::where('key', 'bmt')->first();

        HospitalCapability::create([
            'hospital_id' => $hospital->id,
            'capability_id' => $radioCap->id,
            'status' => HospitalCapabilityStatus::Available,
        ]);
        HospitalCapability::create([
            'hospital_id' => $hospital->id,
            'capability_id' => $bmtCap->id,
            'status' => HospitalCapabilityStatus::NotAvailable,
        ]);

        $response = $this->get(route('hospitals.index'));

        $response->assertOk();
        $response->assertSee('রেডিওথেরাপি');
        $response->assertSee('বোন ম্যারো ট্রান্সপ্ল্যান্ট (BMT) নেই');
    }

    public function test_hospital_profile_page_is_accessible_and_renders_all_sections(): void
    {
        $hospital = $this->createHospital([
            'name_bn' => 'জাতীয় ক্যান্সার গবেষণা ইনস্টিটিউট ও হাসপাতাল',
            'slug' => 'nicrh-dhaka',
        ]);

        // Capability
        $radioCap = Capability::where('key', 'radiotherapy')->first();
        HospitalCapability::create([
            'hospital_id' => $hospital->id,
            'capability_id' => $radioCap->id,
            'status' => HospitalCapabilityStatus::Available,
            'detail_bn' => '৩টি লিনিয়ার অ্যাক্সিলারেটর সচল',
        ]);

        // Wait Time
        HospitalWaitTime::create([
            'hospital_id' => $hospital->id,
            'service_key' => 'radiotherapy_start',
            'label_bn' => 'রেডিওথেরাপির সিরিয়াল পাওয়া',
            'min_weeks' => 4,
            'max_weeks' => 8,
            'severity' => HospitalWaitTimeSeverity::Long,
        ]);

        // Cost
        HospitalCost::create([
            'hospital_id' => $hospital->id,
            'service_key' => 'chemo_per_cycle',
            'label_bn' => 'কেমোথেরাপি (প্রতি সাইকেল)',
            'min_amount' => 500,
            'max_amount' => 2000,
            'note_bn' => 'ওষুধের খরচ আলাদা',
        ]);

        // Prep Info
        HospitalPrepInfo::create([
            'hospital_id' => $hospital->id,
            'key' => HospitalPrepKey::BloodBank,
            'title_bn' => 'ব্লাড ব্যাংক ও ডোনার প্রস্তুতি',
            'description_bn' => '২ জন রক্তদাতা সাথে রাখা বাধ্যতামূলক।',
            'flag_text_bn' => 'ডোনার সাথে থাকা জরুরি',
            'flag_type' => HospitalFlagType::Warning,
        ]);

        // Practical Info
        HospitalPracticalInfo::create([
            'hospital_id' => $hospital->id,
            'key' => HospitalPracticalKey::Documents,
            'title_bn' => 'প্রথম দিনে যা যা সাথে আনবেন',
            'description_bn' => 'এনআইডি ও বায়োপসি রিপোর্ট সঙ্গে আনুন।',
            'icon' => 'file-text',
        ]);

        // Video
        HospitalVideo::create([
            'hospital_id' => $hospital->id,
            'video_url' => 'https://www.youtube.com/watch?v=demo123',
            'title_bn' => 'হাসপাতাল চেনার পূর্ণাঙ্গ ভিডিও গাইড',
            'duration_seconds' => 180,
            'produced_by' => 'CancerCare Bangladesh',
        ]);

        $response = $this->get(route('hospitals.show', $hospital));

        $response->assertOk();
        $response->assertSee('জাতীয় ক্যান্সার গবেষণা ইনস্টিটিউট ও হাসপাতাল');
        $response->assertSee('কী কী চিকিৎসা পাওয়া যায়');
        $response->assertSee('৩টি লিনিয়ার অ্যাক্সিলারেটর সচল');
        $response->assertSee('অপেক্ষার সময়');
        $response->assertSee('রেডিওথেরাপির সিরিয়াল পাওয়া');
        $response->assertSee('আনুমানিক খরচ');
        $response->assertSee('কেমোথেরাপি (প্রতি সাইকেল)');
        $response->assertSee('যেসব বিষয়ে আগে থেকে প্রস্তুতি লাগে');
        $response->assertSee('ব্লাড ব্যাংক ও ডোনার প্রস্তুতি');
        $response->assertSee('বাইরের জেলা থেকে এলে যা জানা দরকার');
        $response->assertSee('হাসপাতাল চেনার পূর্ণাঙ্গ ভিডিও গাইড');
        $response->assertSee('যোগাযোগ ও অবস্থান');
    }

    public function test_hospital_profile_does_not_contain_star_ratings_and_explains_why(): void
    {
        $hospital = $this->createHospital();

        $response = $this->get(route('hospitals.show', $hospital));

        $response->assertOk();
        // Verifying explanation is present
        $response->assertSee('আমরা কোনো স্টার রেটিং দিই না');
        $response->assertSee('কারণ ৳২০-র সরকারি হাসপাতাল আর ৳১,০০০-এর বেসরকারি হাসপাতালকে এক সংখ্যায় তুলনা করা যায় না');
    }

    public function test_hospital_profile_renders_structured_experience_percentages(): void
    {
        $hospital = $this->createHospital();
        $question = HospitalExperienceQuestion::first();
        $this->assertNotNull($question);

        HospitalExperienceSummary::create([
            'hospital_id' => $hospital->id,
            'question_id' => $question->id,
            'yes_count' => 24,
            'total_count' => 30,
            'percentage' => 80.00,
            'is_published' => true,
        ]);

        $response = $this->get(route('hospitals.show', $hospital));

        $response->assertOk();
        $response->assertSee($question->label_bn);
        $response->assertSee('80% বলেছেন হ্যাঁ');
    }

    public function test_hospital_profile_returns_404_for_draft_or_non_existent_hospital(): void
    {
        $draftHospital = $this->createHospital([
            'status' => HospitalStatus::Draft,
        ]);

        $response = $this->get(route('hospitals.show', $draftHospital));
        $response->assertNotFound();

        $response404 = $this->get('/hospitals/non-existent-slug');
        $response404->assertNotFound();
    }
}
