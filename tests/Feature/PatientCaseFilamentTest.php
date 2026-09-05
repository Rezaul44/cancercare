<?php

namespace Tests\Feature;

use App\Enums\PatientCaseAccountType;
use App\Enums\PatientCaseGender;
use App\Enums\PatientCaseStatus;
use App\Enums\PatientCaseVerificationStatus;
use App\Enums\PatientCaseVerificationStep;
use App\Filament\Resources\PatientCaseResource;
use App\Models\CancerType;
use App\Models\District;
use App\Models\Division;
use App\Models\PatientCase;
use App\Models\PatientCaseAccount;
use App\Models\PatientCaseVerification;
use App\Models\User;
use App\Services\VerificationService;
use Database\Seeders\RolePermissionSeeder;
use DomainException;
use Filament\Pages\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PatientCaseFilamentTest extends TestCase
{
    use RefreshDatabase;

    protected CancerType $cancerType;
    protected District $district;
    protected User $superAdmin;
    protected User $fieldAgent;
    protected User $verificationOfficer;
    protected VerificationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->service = app(VerificationService::class);

        $division = Division::create([
            'name_bn' => 'ঢাকা',
            'name_en' => 'Dhaka',
            'slug' => 'dhaka',
        ]);

        $this->district = District::create([
            'division_id' => $division->id,
            'name_bn' => 'ঢাকা',
            'name_en' => 'Dhaka',
            'slug' => 'dhaka-dist',
            'distance_tier' => 'local',
            'has_cancer_center' => true,
        ]);

        $this->cancerType = CancerType::create([
            'name_bn' => 'স্তন ক্যান্সার',
            'name_en' => 'Breast Cancer',
            'slug' => 'breast-cancer',
            'icon' => 'ribbon',
            'color_key' => 'pink',
            'short_description_bn' => 'স্তন ক্যান্সারের বিবরণ',
            'gender_bias' => 'female',
            'is_common' => true,
            'guide_published' => true,
            'sort_order' => 1,
        ]);

        $this->superAdmin = User::factory()->create([
            'email' => 'superadmin@cancercare.test',
        ]);
        $this->superAdmin->assignRole('super_admin');

        $this->verificationOfficer = User::factory()->create([
            'email' => 'officer@cancercare.test',
        ]);
        $this->verificationOfficer->assignRole('verification_officer');

        $this->fieldAgent = User::factory()->create([
            'email' => 'fieldagent@cancercare.test',
        ]);
        $this->fieldAgent->assignRole('field_agent');
    }

    public function test_can_publish_case_validations_across_all_conditions(): void
    {
        $case = PatientCase::create([
            'real_name' => 'সুলতানা রাজিয়া',
            'display_name_bn' => 'সুলতানা বেগম',
            'age' => 45,
            'gender' => PatientCaseGender::Female,
            'cancer_type_id' => $this->cancerType->id,
            'district_id' => $this->district->id,
            'story_bn' => 'রোগীর চিকিৎসার জন্য আর্থিক সহায়তা প্রয়োজন।',
            'amount_needed' => 150000,
            'status' => PatientCaseStatus::Verifying,
            'created_by' => $this->fieldAgent->id,
        ]);

        // 1. Initially no verification steps, consent form, or accounts -> CANNOT publish
        $this->assertFalse($this->service->canPublishCase($case));
        $missing = $this->service->getMissingRequirements($case);
        $this->assertCount(6, $missing); // 4 steps + consent form + verified account

        // 2. Add only 3 verification steps as done
        foreach (['documents', 'hospital_confirm', 'identity'] as $step) {
            PatientCaseVerification::create([
                'patient_case_id' => $case->id,
                'step' => $step,
                'status' => PatientCaseVerificationStatus::Done,
                'completed_by' => $this->verificationOfficer->id,
                'completed_at' => now(),
            ]);
        }

        $case->refresh();
        $this->assertFalse($this->service->canPublishCase($case));

        // 3. Complete 4th verification step (field_meeting)
        PatientCaseVerification::create([
            'patient_case_id' => $case->id,
            'step' => PatientCaseVerificationStep::FieldMeeting,
            'status' => PatientCaseVerificationStatus::Done,
            'completed_by' => $this->verificationOfficer->id,
            'completed_at' => now(),
        ]);

        $case->refresh();
        // Still missing consent form and account
        $this->assertFalse($this->service->canPublishCase($case));

        // 4. Upload consent form
        $case->consent_form_path = 'patient-cases/consent/sultana_consent.pdf';
        $case->consent_signed_at = now();
        $case->save();

        $case->refresh();
        // Still missing verified account
        $this->assertFalse($this->service->canPublishCase($case));

        // 5. Add unverified account
        $account = PatientCaseAccount::create([
            'patient_case_id' => $case->id,
            'type' => PatientCaseAccountType::Bkash,
            'account_number' => '01711111111',
            'account_name' => 'সুলতানা রাজিয়া',
            'name_verified' => false,
            'is_active' => true,
        ]);

        $case->refresh();
        $this->assertFalse($this->service->canPublishCase($case));

        // 6. Verify account name
        $account->update(['name_verified' => true]);

        $case->refresh();
        // Now all criteria fulfilled!
        $this->assertTrue($this->service->canPublishCase($case));
        $this->assertEmpty($this->service->getMissingRequirements($case));
    }

    public function test_publish_case_sets_published_status_and_timestamps(): void
    {
        $now = Carbon::create(2026, 9, 2, 12, 0, 0);
        Carbon::setTestNow($now);

        $case = PatientCase::create([
            'real_name' => 'মো. করিম',
            'display_name_bn' => 'করিম সাহেব',
            'age' => 50,
            'gender' => PatientCaseGender::Male,
            'cancer_type_id' => $this->cancerType->id,
            'district_id' => $this->district->id,
            'story_bn' => 'চিকিৎসার বর্ণনা।',
            'amount_needed' => 120000,
            'consent_form_path' => 'patient-cases/consent/karim_consent.pdf',
            'status' => PatientCaseStatus::Verifying,
            'created_by' => $this->fieldAgent->id,
        ]);

        // Complete 4 steps
        foreach (VerificationService::REQUIRED_STEPS as $step) {
            PatientCaseVerification::create([
                'patient_case_id' => $case->id,
                'step' => $step,
                'status' => PatientCaseVerificationStatus::Done,
                'completed_by' => $this->verificationOfficer->id,
                'completed_at' => $now,
            ]);
        }

        // Verified Account
        PatientCaseAccount::create([
            'patient_case_id' => $case->id,
            'type' => PatientCaseAccountType::Nagad,
            'account_number' => '01822222222',
            'account_name' => 'মো. করিম',
            'name_verified' => true,
            'is_active' => true,
        ]);

        $case->refresh();
        $this->assertTrue($this->service->canPublishCase($case));

        // Publish case through service
        $published = $this->service->publishCase($case, $this->verificationOfficer);
        $this->assertTrue($published);

        $case->refresh();
        $this->assertEquals(PatientCaseStatus::Published, $case->status);
        $this->assertEquals($this->verificationOfficer->id, $case->verified_by);
        $this->assertNotNull($case->verified_at);
        $this->assertEquals('2026-09-02 12:00:00', $case->published_at->format('Y-m-d H:i:s'));
        $this->assertEquals('2026-10-02 12:00:00', $case->expires_at->format('Y-m-d H:i:s'));

        Carbon::setTestNow();
    }

    public function test_publish_case_throws_exception_if_not_publishable(): void
    {
        $case = PatientCase::create([
            'real_name' => 'অসম্পূর্ণ রোগী',
            'display_name_bn' => 'রোগী',
            'age' => 30,
            'gender' => PatientCaseGender::Male,
            'cancer_type_id' => $this->cancerType->id,
            'district_id' => $this->district->id,
            'story_bn' => 'বিবরণ',
            'amount_needed' => 50000,
            'status' => PatientCaseStatus::Draft,
            'created_by' => $this->fieldAgent->id,
        ]);

        $this->expectException(DomainException::class);
        $this->service->publishCase($case, $this->superAdmin);
    }

    public function test_verification_progress_calculation(): void
    {
        $case = PatientCase::create([
            'real_name' => 'প্রগ্রেস টেস্ট রোগী',
            'display_name_bn' => 'প্রগ্রেস টেস্ট',
            'age' => 40,
            'gender' => PatientCaseGender::Female,
            'cancer_type_id' => $this->cancerType->id,
            'district_id' => $this->district->id,
            'story_bn' => 'বিবরণ',
            'amount_needed' => 60000,
            'status' => PatientCaseStatus::Draft,
            'created_by' => $this->fieldAgent->id,
        ]);

        // 0 steps
        $progress = $this->service->getVerificationProgress($case);
        $this->assertEquals(0, $progress['done_count']);
        $this->assertEquals(0, $progress['percentage']);

        // 2 steps
        PatientCaseVerification::create([
            'patient_case_id' => $case->id,
            'step' => PatientCaseVerificationStep::Documents,
            'status' => PatientCaseVerificationStatus::Done,
        ]);
        PatientCaseVerification::create([
            'patient_case_id' => $case->id,
            'step' => PatientCaseVerificationStep::HospitalConfirm,
            'status' => PatientCaseVerificationStatus::Done,
        ]);

        $case->refresh();
        $progress = $this->service->getVerificationProgress($case);
        $this->assertEquals(2, $progress['done_count']);
        $this->assertEquals(50, $progress['percentage']);
    }

    public function test_role_permissions_field_agent_can_create_but_cannot_publish(): void
    {
        // Field Agent can view and create cases
        $this->assertTrue($this->fieldAgent->can('cases.view'));
        $this->assertTrue($this->fieldAgent->can('cases.create'));

        // Field Agent CANNOT publish or verify cases
        $this->assertFalse($this->fieldAgent->can('cases.publish'));
        $this->assertFalse($this->fieldAgent->can('cases.verify'));
        $this->assertFalse($this->fieldAgent->can('cases.manage'));

        // Verification Officer & Super Admin CAN publish
        $this->assertTrue($this->verificationOfficer->can('cases.publish'));
        $this->assertTrue($this->superAdmin->can('cases.publish'));
    }

    public function test_filament_patient_case_resource_authorization(): void
    {
        $this->actingAs($this->superAdmin);
        $this->assertTrue(PatientCaseResource::canViewAny());
        $this->assertTrue(PatientCaseResource::canCreate());

        $this->actingAs($this->fieldAgent);
        $this->assertTrue(PatientCaseResource::canViewAny());
        $this->assertTrue(PatientCaseResource::canCreate());
        $this->assertFalse($this->fieldAgent->can('cases.publish'));
    }
}
