<?php

namespace Tests\Feature;

use App\Enums\ChamberType;
use App\Enums\DoctorStatus;
use App\Models\CancerType;
use App\Models\Chamber;
use App\Models\Doctor;
use App\Models\DoctorCancerTypeStage;
use App\Models\DoctorPhilosophyPoint;
use App\Models\DoctorRatingSummary;
use App\Models\DoctorTreatmentSpecialty;
use App\Models\DoctorType;
use App\Models\DoctorVideo;
use App\Models\RatingCriteria;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * /doctors/{slug} — docs/prototypes/doctor_profile.html। রেটিং সেকশন শুধু is_published হলে
 * দেখানো হয় (CLAUDE.md নীতি ৫) এবং ম্যাচ ইঞ্জিনের AJAX endpoint (DoctorProfileController::match)
 * এই টেস্টে যাচাই হয়।
 */
class DoctorProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_doctor_profile_renders_all_sections(): void
    {
        $doctor = $this->makeDoctor(['offers_second_opinion' => true, 'offers_whatsapp' => true]);
        $cancerType = $this->makeCancerType();
        $doctor->cancerTypes()->attach($cancerType->id, ['is_primary' => true]);
        $this->makeService($doctor, $cancerType);
        $this->makePhilosophyPoint($doctor);
        $this->makeTimelineEntry($doctor);
        $district = $this->makeDistrict();
        $this->makeChamber($doctor, $district);
        DoctorVideo::create([
            'doctor_id' => $doctor->id, 'type' => 'intro', 'platform' => 'youtube',
            'video_url' => 'https://youtube.com/x', 'title_bn' => 'পরিচিতি ভিডিও',
            'duration_seconds' => 124, 'produced_by' => 'Doctor Pro Media', 'sort_order' => 1,
        ]);
        DoctorVideo::create([
            'doctor_id' => $doctor->id, 'type' => 'educational', 'platform' => 'youtube',
            'video_url' => 'https://youtube.com/y', 'title_bn' => 'শিক্ষামূলক ভিডিও শিরোনাম',
            'duration_seconds' => 300, 'view_count' => 500, 'sort_order' => 2,
        ]);

        $response = $this->get(route('doctors.show', $doctor));

        $response->assertOk();
        $response->assertSee($doctor->name_bn);
        $response->assertSee($doctor->degrees_line_bn);
        $response->assertSee('এই ডাক্তার কি আমার জন্য');
        $response->assertSee('যেসব চিকিৎসা করেন');
        $response->assertSee('পেশাগত জীবন');
        $response->assertSee('শিক্ষামূলক ভিডিও');
        $response->assertSee('চিকিৎসা দর্শন');
        $response->assertSee('Doctor Pro Media প্রযোজিত');
        $response->assertSee('তালিকার ক্রমে প্রভাব ফেলে না');
        $response->assertSee('পরিচয়, ডিগ্রি ও চেম্বারের তথ্য ডাক্তার নিজে দিয়েছেন');
    }

    public function test_unpublished_doctor_returns_404(): void
    {
        $doctor = $this->makeDoctor(['status' => DoctorStatus::Draft]);

        $response = $this->get(route('doctors.show', $doctor));

        $response->assertNotFound();
    }

    public function test_rating_section_hidden_when_not_published(): void
    {
        $doctor = $this->makeDoctor();
        DoctorRatingSummary::create([
            'doctor_id' => $doctor->id, 'total_count' => 3, 'criteria_scores' => [],
            'overall_score' => null, 'is_published' => false, 'last_calculated_at' => now(),
        ]);

        $response = $this->get(route('doctors.show', $doctor));

        $response->assertOk();
        // "রোগীদের মতামত" নিচের স্থায়ী উৎস-নোটেও থাকে (prototype-এর সাথে মিলিয়ে), তাই রেটিং
        // সেকশনের জন্যই নির্দিষ্ট একটা লাইন দিয়ে যাচাই করা হচ্ছে।
        $response->assertDontSee('শুধু যাচাইকৃত রোগীরাই মতামত দিতে পারেন');
    }

    public function test_rating_section_shown_when_published_with_criteria_scores(): void
    {
        RatingCriteria::create(['key' => 'explains_clearly', 'label_bn' => 'বুঝিয়ে বলেন', 'sort_order' => 1, 'is_active' => true]);

        $doctor = $this->makeDoctor();
        DoctorRatingSummary::create([
            'doctor_id' => $doctor->id, 'total_count' => 12, 'criteria_scores' => ['explains_clearly' => 96],
            'overall_score' => 4.8, 'is_published' => true, 'last_calculated_at' => now(),
        ]);

        $response = $this->get(route('doctors.show', $doctor));

        $response->assertOk();
        $response->assertSee('রোগীদের মতামত');
        $response->assertSee('4.8');
        $response->assertSee('বুঝিয়ে বলেন');
        $response->assertSee('96%', false);
    }

    public function test_schema_org_physician_markup_is_present(): void
    {
        $doctor = $this->makeDoctor();

        $response = $this->get(route('doctors.show', $doctor));

        $response->assertOk();
        $response->assertSee('application/ld+json', false);
        $response->assertSee('"@type": "Physician"', false);
        $response->assertSee($doctor->name_bn, false);
    }

    public function test_match_endpoint_returns_not_a_match_for_unrelated_cancer_type(): void
    {
        $doctor = $this->makeDoctor();
        $breast = CancerType::create($this->cancerTypePayload('breast-cancer', 'স্তন'));

        $response = $this->getJson(route('ajax.doctors.match', $doctor).'?cancer=breast-cancer');

        $response->assertOk();
        $response->assertJson(['ok' => 0, 'stage' => null, 'treatment' => null]);
    }

    public function test_match_endpoint_returns_stage_data_for_matching_cancer_type(): void
    {
        $doctor = $this->makeDoctor();
        $breast = CancerType::create($this->cancerTypePayload('breast-cancer', 'স্তন'));
        $doctor->cancerTypes()->attach($breast->id, ['is_primary' => true]);

        DoctorCancerTypeStage::create([
            'doctor_id' => $doctor->id, 'cancer_type_id' => $breast->id, 'stage' => '2',
            'case_count' => 340, 'success_rate_percent' => 89, 'note_bn' => 'পরীক্ষার নোট',
        ]);
        DoctorTreatmentSpecialty::create([
            'doctor_id' => $doctor->id, 'cancer_type_id' => $breast->id, 'treatment_key' => 'radiation',
            'role' => 'refers', 'note_bn' => 'রেডিয়েশন বিশেষজ্ঞের কাছে পাঠান।',
        ]);

        $response = $this->getJson(route('ajax.doctors.match', $doctor).'?cancer=breast-cancer&stage=2&treatment=radiation');

        $response->assertOk();
        $response->assertJson([
            'ok' => 1,
            'stage' => ['case_count' => 340, 'success_rate_percent' => 89, 'note_bn' => 'পরীক্ষার নোট'],
            'treatment' => ['role' => 'refers', 'note_bn' => 'রেডিয়েশন বিশেষজ্ঞের কাছে পাঠান।'],
        ]);
    }

    public function test_match_endpoint_treats_other_as_uncertain(): void
    {
        $doctor = $this->makeDoctor();

        $response = $this->getJson(route('ajax.doctors.match', $doctor).'?cancer=other');

        $response->assertOk();
        $response->assertJson(['ok' => 2, 'stage' => null, 'treatment' => null]);
    }

    public function test_match_endpoint_returns_null_ok_when_nothing_selected(): void
    {
        $doctor = $this->makeDoctor();

        $response = $this->getJson(route('ajax.doctors.match', $doctor));

        $response->assertOk();
        $response->assertJson(['ok' => null, 'stage' => null, 'treatment' => null]);
    }

    public function test_match_endpoint_404s_for_unpublished_doctor(): void
    {
        $doctor = $this->makeDoctor(['status' => DoctorStatus::Draft]);

        $response = $this->getJson(route('ajax.doctors.match', $doctor).'?cancer=breast-cancer');

        $response->assertNotFound();
    }

    private function makeDoctor(array $overrides = []): Doctor
    {
        return Doctor::create(array_merge([
            'name_bn' => 'ডা. পরীক্ষা '.uniqid(),
            'name_en' => 'Test Doctor '.uniqid(),
            'slug' => 'test-doctor-'.uniqid(),
            'bmdc_number' => 'BMDC-'.uniqid(),
            'bmdc_verified_at' => now()->subMonth(),
            'photo_path' => 'doctors/photo.jpg',
            'degrees_line_bn' => 'এমবিবিএস, এফসিপিএস',
            'experience_years' => 10,
            'current_position_bn' => 'কনসালট্যান্ট',
            'gender' => 'female',
            'patients_treated' => 500,
            'status' => DoctorStatus::Published,
            'doctor_approved_at' => now()->subMonth(),
        ], $overrides));
    }

    private function makeCancerType(): CancerType
    {
        return CancerType::create($this->cancerTypePayload('cancer-'.uniqid(), 'পরীক্ষা'));
    }

    /**
     * @return array<string, mixed>
     */
    private function cancerTypePayload(string $slug, string $nameBn): array
    {
        return [
            'name_bn' => $nameBn, 'name_en' => 'Test Cancer', 'slug' => $slug,
            'icon' => 'ribbon', 'color_key' => 'pink', 'short_description_bn' => 'বিবরণ',
            'is_common' => true, 'doctor_count_cache' => 0, 'hospital_count_cache' => 0,
            'guide_published' => false, 'sort_order' => 1,
        ];
    }

    private function makeDistrict(): \App\Models\District
    {
        $divisionId = \Illuminate\Support\Facades\DB::table('divisions')->insertGetId([
            'name_bn' => 'ঢাকা', 'name_en' => 'Dhaka', 'slug' => 'division-'.uniqid(),
        ]);

        return \App\Models\District::create([
            'division_id' => $divisionId,
            'name_bn' => 'ঢাকা', 'name_en' => 'Dhaka', 'slug' => 'dhaka-'.uniqid(),
            'distance_tier' => 'local', 'has_cancer_center' => true,
        ]);
    }

    private function makeChamber(Doctor $doctor, \App\Models\District $district): Chamber
    {
        return Chamber::create([
            'doctor_id' => $doctor->id,
            'name_bn' => 'চেম্বার', 'address_bn' => 'ঠিকানা', 'district_id' => $district->id,
            'type' => ChamberType::Private, 'fee' => 500, 'days_bn' => 'রবি',
            'time_from' => '09:00', 'time_to' => '13:00', 'is_active' => true, 'sort_order' => 1,
        ]);
    }

    private function makeService(Doctor $doctor, CancerType $cancerType): void
    {
        \App\Models\DoctorService::create([
            'doctor_id' => $doctor->id, 'cancer_type_id' => $cancerType->id,
            'title_bn' => 'সেবার শিরোনাম', 'description_bn' => 'বিবরণ',
            'badge_color' => 'teal', 'icon' => 'stethoscope', 'sort_order' => 1,
        ]);
    }

    private function makePhilosophyPoint(Doctor $doctor): void
    {
        DoctorPhilosophyPoint::create([
            'doctor_id' => $doctor->id, 'icon' => 'heart', 'title_bn' => 'দর্শনের শিরোনাম',
            'description_bn' => 'বিবরণ', 'sort_order' => 1,
        ]);
    }

    private function makeTimelineEntry(Doctor $doctor): void
    {
        \App\Models\DoctorTimeline::create([
            'doctor_id' => $doctor->id, 'year_label' => '২০১০', 'title_bn' => 'এমবিবিএস',
            'institution_bn' => 'ঢাকা মেডিকেল কলেজ', 'sort_order' => 1,
        ]);
    }
}
