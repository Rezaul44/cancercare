<?php

namespace Tests\Feature;

use App\Enums\DoctorStatus;
use App\Models\CancerType;
use App\Models\Doctor;
use App\Models\DoctorCancerTypeStage;
use App\Models\DoctorTreatmentSpecialty;
use App\Services\DoctorMatchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 2.7 ম্যাচ ইঞ্জিন টেস্ট — docs/CCB_prompt_playbook.md ধারা ২.৭
 *
 * DoctorMatchService ও AJAX endpoint GET /ajax/doctors/{slug}/match পরীক্ষা করে:
 * - ক্যান্সার টাইপ না মিললে "উপযুক্ত নন" (ok: 0) + ডিরেক্টরি রিডাইরেক্ট লিঙ্ক
 * - ক্যান্সার টাইপ মিললে কিন্তু ট্রিটমেন্ট রেফারেল/স্টেজ ৪ হলে "ভালো মিল, কিছু বিবেচনা" (ok: 1, verdict: partial)
 * - সব মিললে "উপযুক্ত" (ok: 1, verdict: suitable)
 * - 'other' বা ফাঁকা থাকলে যথাক্রমে ok: 2 ও ok: null
 * - কোনো ভুয়া সাকসেস রেট নেই, শুধু কেস কাউন্ট ও নোট
 */
class DoctorMatchServiceTest extends TestCase
{
    use RefreshDatabase;

    private DoctorMatchService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DoctorMatchService();
    }

    public function test_returns_null_verdict_when_no_cancer_selected(): void
    {
        $doctor = $this->createPublishedDoctor();

        $result = $this->service->match($doctor, null, null, null);

        $this->assertNull($result['ok']);
        $this->assertNull($result['verdict']);
        $this->assertNull($result['stage']);
        $this->assertNull($result['treatment']);
    }

    public function test_returns_uncertain_verdict_when_cancer_type_is_other(): void
    {
        $doctor = $this->createPublishedDoctor();

        $result = $this->service->match($doctor, 'other', '2', 'surgery');

        $this->assertSame(2, $result['ok']);
        $this->assertSame('uncertain', $result['verdict']);
        $this->assertNull($result['stage']);
        $this->assertNull($result['treatment']);
        $this->assertSame(route('doctors.index'), $result['redirect_url']);
    }

    public function test_returns_not_a_match_verdict_when_doctor_does_not_treat_cancer_type(): void
    {
        $doctor = $this->createPublishedDoctor();
        $lung = $this->createCancerType('lung-cancer', 'ফুসফুসের ক্যান্সার');

        $result = $this->service->match($doctor, 'lung-cancer', '2', 'surgery');

        $this->assertSame(0, $result['ok']);
        $this->assertSame('not_a_match', $result['verdict']);
        $this->assertNull($result['stage']);
        $this->assertNull($result['treatment']);
        $this->assertSame(route('doctors.index', ['cancer' => 'lung-cancer']), $result['redirect_url']);
        $this->assertStringContainsString('উপযুক্ত নন', $result['message']);
    }

    public function test_returns_suitable_verdict_when_full_match_with_stage_and_provided_treatment(): void
    {
        $doctor = $this->createPublishedDoctor();
        $breast = $this->createCancerType('breast-cancer', 'স্তন ক্যান্সার');
        $doctor->cancerTypes()->attach($breast->id, ['is_primary' => true]);

        DoctorCancerTypeStage::create([
            'doctor_id' => $doctor->id,
            'cancer_type_id' => $breast->id,
            'stage' => '2',
            'case_count' => 340,
            'success_rate_percent' => 89,
            'note_bn' => 'এই স্টেজেই সবচেয়ে বেশি অভিজ্ঞ।',
        ]);

        DoctorTreatmentSpecialty::create([
            'doctor_id' => $doctor->id,
            'cancer_type_id' => $breast->id,
            'treatment_key' => 'surgery',
            'role' => 'provides',
            'note_bn' => 'সার্জিক্যাল অনকোলজিস্ট হিসেবে নিজেই অপারেশন করেন।',
        ]);

        $result = $this->service->match($doctor, 'breast-cancer', '2', 'surgery');

        $this->assertSame(1, $result['ok']);
        $this->assertSame('suitable', $result['verdict']);
        $this->assertNotNull($result['stage']);
        $this->assertSame(340, $result['stage']['case_count']);
        $this->assertSame('এই স্টেজেই সবচেয়ে বেশি অভিজ্ঞ।', $result['stage']['note_bn']);
        $this->assertNotNull($result['treatment']);
        $this->assertSame('provides', $result['treatment']['role']);
        $this->assertStringContainsString('উপযুক্ত', $result['message']);
    }

    public function test_returns_partial_verdict_when_treatment_is_referral(): void
    {
        $doctor = $this->createPublishedDoctor();
        $breast = $this->createCancerType('breast-cancer', 'স্তন ক্যান্সার');
        $doctor->cancerTypes()->attach($breast->id, ['is_primary' => true]);

        DoctorCancerTypeStage::create([
            'doctor_id' => $doctor->id,
            'cancer_type_id' => $breast->id,
            'stage' => '2',
            'case_count' => 340,
            'success_rate_percent' => 89,
            'note_bn' => 'নিয়মিত চিকিৎসা করেন।',
        ]);

        DoctorTreatmentSpecialty::create([
            'doctor_id' => $doctor->id,
            'cancer_type_id' => $breast->id,
            'treatment_key' => 'radiation',
            'role' => 'refers',
            'note_bn' => 'রেডিয়েশন নিজে দেন না — রেডিয়েশন বিশেষজ্ঞের কাছে পাঠান।',
        ]);

        $result = $this->service->match($doctor, 'breast-cancer', '2', 'radiation');

        $this->assertSame(1, $result['ok']);
        $this->assertSame('partial', $result['verdict']);
        $this->assertSame('refers', $result['treatment']['role']);
        $this->assertStringContainsString('বিবেচনা', $result['message']);
    }

    public function test_returns_partial_verdict_when_stage_is_4(): void
    {
        $doctor = $this->createPublishedDoctor();
        $breast = $this->createCancerType('breast-cancer', 'স্তন ক্যান্সার');
        $doctor->cancerTypes()->attach($breast->id, ['is_primary' => true]);

        DoctorCancerTypeStage::create([
            'doctor_id' => $doctor->id,
            'cancer_type_id' => $breast->id,
            'stage' => '4',
            'case_count' => 40,
            'success_rate_percent' => 45,
            'note_bn' => 'স্টেজ ৪ জটিল, জীবনের মান ভালো রাখাই মূল লক্ষ্য।',
        ]);

        $result = $this->service->match($doctor, 'breast-cancer', '4', null);

        $this->assertSame(1, $result['ok']);
        $this->assertSame('partial', $result['verdict']);
        $this->assertSame(40, $result['stage']['case_count']);
    }

    public function test_falls_back_to_unknown_stage_when_specific_stage_not_in_db(): void
    {
        $doctor = $this->createPublishedDoctor();
        $breast = $this->createCancerType('breast-cancer', 'স্তন ক্যান্সার');
        $doctor->cancerTypes()->attach($breast->id, ['is_primary' => true]);

        DoctorCancerTypeStage::create([
            'doctor_id' => $doctor->id,
            'cancer_type_id' => $breast->id,
            'stage' => 'unknown',
            'case_count' => 200,
            'success_rate_percent' => 80,
            'note_bn' => 'সব স্টেজেই চিকিৎসা দেন।',
        ]);

        // Specific stage '3' isn't explicitly entered, but 'unknown' fallback exists
        $result = $this->service->match($doctor, 'breast-cancer', '3', null);

        $this->assertSame(1, $result['ok']);
        $this->assertNotNull($result['stage']);
        $this->assertSame(200, $result['stage']['case_count']);
    }

    public function test_ajax_endpoint_returns_json_matching_service_verdict(): void
    {
        $doctor = $this->createPublishedDoctor();
        $breast = $this->createCancerType('breast-cancer', 'স্তন ক্যান্সার');
        $doctor->cancerTypes()->attach($breast->id, ['is_primary' => true]);

        DoctorCancerTypeStage::create([
            'doctor_id' => $doctor->id,
            'cancer_type_id' => $breast->id,
            'stage' => '1',
            'case_count' => 120,
            'success_rate_percent' => 95,
            'note_bn' => 'প্রাথমিক পর্যায়।',
        ]);

        DoctorTreatmentSpecialty::create([
            'doctor_id' => $doctor->id,
            'cancer_type_id' => $breast->id,
            'treatment_key' => 'surgery',
            'role' => 'provides',
            'note_bn' => 'স্তন রক্ষা করে সার্জারি করেন।',
        ]);

        $response = $this->getJson(route('ajax.doctors.match', $doctor).'?cancer=breast-cancer&stage=1&treatment=surgery');

        $response->assertOk();
        $response->assertJson([
            'ok' => 1,
            'verdict' => 'suitable',
            'stage' => [
                'case_count' => 120,
                'note_bn' => 'প্রাথমিক পর্যায়।',
            ],
            'treatment' => [
                'role' => 'provides',
                'note_bn' => 'স্তন রক্ষা করে সার্জারি করেন।',
            ],
        ]);
    }

    public function test_ajax_endpoint_returns_404_for_draft_doctor(): void
    {
        $doctor = $this->createPublishedDoctor(['status' => DoctorStatus::Draft]);

        $response = $this->getJson(route('ajax.doctors.match', $doctor).'?cancer=breast-cancer');

        $response->assertNotFound();
    }

    private function createPublishedDoctor(array $overrides = []): Doctor
    {
        return Doctor::create(array_merge([
            'name_bn' => 'ডা. সাদিয়া রহমান',
            'name_en' => 'Dr. Sadia Rahman',
            'slug' => 'dr-sadia-rahman-'.uniqid(),
            'bmdc_number' => 'BMDC-'.uniqid(),
            'bmdc_verified_at' => now()->subMonths(2),
            'photo_path' => 'doctors/photo.jpg',
            'degrees_line_bn' => 'এমবিবিএস, এফসিপিএস (সার্জারি), এমএস',
            'experience_years' => 12,
            'current_position_bn' => 'সহকারী অধ্যাপক, সার্জিক্যাল অনকোলজি',
            'gender' => 'female',
            'patients_treated' => 500,
            'status' => DoctorStatus::Published,
            'doctor_approved_at' => now()->subMonths(2),
        ], $overrides));
    }

    private function createCancerType(string $slug, string $nameBn): CancerType
    {
        return CancerType::create([
            'slug' => $slug,
            'name_bn' => $nameBn,
            'name_en' => 'Cancer',
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
