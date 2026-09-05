<?php

namespace Tests\Feature;

use App\Enums\HospitalCapabilityStatus;
use App\Enums\HospitalFlagType;
use App\Enums\HospitalPracticalKey;
use App\Enums\HospitalPrepKey;
use App\Enums\HospitalStatus;
use App\Enums\HospitalType;
use App\Enums\HospitalWaitTimeSeverity;
use App\Filament\Resources\HospitalResource;
use App\Filament\Resources\HospitalResource\Pages\CreateHospital;
use App\Filament\Resources\HospitalResource\Pages\EditHospital;
use App\Filament\Resources\HospitalResource\Pages\ListHospitals;
use App\Filament\Resources\HospitalResource\Pages\ViewHospital;
use App\Filament\Resources\HospitalResource\RelationManagers\CapabilitiesRelationManager;
use App\Filament\Resources\HospitalResource\RelationManagers\CostsRelationManager;
use App\Filament\Resources\HospitalResource\RelationManagers\DoctorsRelationManager;
use App\Filament\Resources\HospitalResource\RelationManagers\ExperienceSummariesRelationManager;
use App\Filament\Resources\HospitalResource\RelationManagers\PracticalInfoRelationManager;
use App\Filament\Resources\HospitalResource\RelationManagers\PrepInfoRelationManager;
use App\Filament\Resources\HospitalResource\RelationManagers\VideosRelationManager;
use App\Filament\Resources\HospitalResource\RelationManagers\WaitTimesRelationManager;
use App\Models\Capability;
use App\Models\District;
use App\Models\Division;
use App\Models\Doctor;
use App\Models\Hospital;
use App\Models\HospitalCapability;
use App\Models\HospitalCost;
use App\Models\HospitalExperienceQuestion;
use App\Models\HospitalExperienceResponse;
use App\Models\HospitalPracticalInfo;
use App\Models\HospitalPrepInfo;
use App\Models\HospitalVideo;
use App\Models\HospitalWaitTime;
use App\Models\User;
use App\Services\HospitalService;
use Database\Seeders\CapabilitySeeder;
use Database\Seeders\HospitalExperienceQuestionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HospitalResourceTest extends TestCase
{
    use RefreshDatabase;

    protected District $district;

    protected function setUp(): void
    {
        parent::setUp();

        (new RolePermissionSeeder)->run();
        (new CapabilitySeeder)->run();
        (new HospitalExperienceQuestionSeeder)->run();
        (new \Database\Seeders\DivisionDistrictSeeder)->run();

        $this->district = District::where('slug', 'dhaka')->first();
    }

    private function makeUser(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function makeHospital(string $slug = 'test-hospital'): Hospital
    {
        return Hospital::create([
            'name_bn' => 'পরীক্ষামূলক ক্যান্সার হাসপাতাল',
            'name_en' => 'Test Cancer Hospital',
            'slug' => $slug,
            'type' => HospitalType::Govt,
            'district_id' => $this->district->id,
            'address_bn' => 'মহাখালী, ঢাকা',
            'phone' => '০২-৯৮৯৮৬০১',
            'established_year' => 1990,
            'bed_count' => 300,
            'oncologist_count' => 25,
            'outdoor_fee' => 50,
            'emergency_24h' => true,
            'annual_patients' => '৫০,০০০+',
            'description_bn' => 'হাসপাতাল পরিচিতি ও বিশেষায়িত সেবার বিবরণ।',
            'status' => HospitalStatus::Published,
            'last_verified_at' => now(),
        ]);
    }

    private function makeDoctor(): Doctor
    {
        return Doctor::create([
            'name_bn' => 'ডা. সাদিয়া রহমান',
            'name_en' => 'Dr. Sadia Rahman',
            'slug' => 'dr-sadia-rahman-'.uniqid(),
            'bmdc_number' => 'BMDC-'.rand(10000, 99999),
            'bmdc_verified_at' => now(),
            'doctor_approved_at' => now(),
            'published_at' => now(),
            'photo_path' => 'doctors/photo.jpg',
            'degrees_line_bn' => 'এমবিবিএস, এফসিপিএস',
            'experience_years' => 12,
            'current_position_bn' => 'সহযোগী অধ্যাপক',
            'gender' => 'female',
            'status' => \App\Enums\DoctorStatus::Published,
            'rotation_seed' => 100,
        ]);
    }

    public function test_super_admin_can_access_hospital_resource_pages(): void
    {
        $admin = $this->makeUser('super_admin');
        $hospital = $this->makeHospital();

        $this->actingAs($admin);

        $this->get(HospitalResource::getUrl('index'))->assertOk();
        $this->get(HospitalResource::getUrl('create'))->assertOk();
        $this->get(HospitalResource::getUrl('edit', ['record' => $hospital]))->assertOk();
        $this->get(HospitalResource::getUrl('view', ['record' => $hospital]))->assertOk();
    }

    public function test_hospital_manager_can_access_and_manage_hospitals(): void
    {
        $manager = $this->makeUser('hospital_manager');
        $hospital = $this->makeHospital();

        $this->actingAs($manager);

        $this->get(HospitalResource::getUrl('index'))->assertOk();
        $this->get(HospitalResource::getUrl('create'))->assertOk();
        $this->get(HospitalResource::getUrl('edit', ['record' => $hospital]))->assertOk();
        $this->get(HospitalResource::getUrl('view', ['record' => $hospital]))->assertOk();
    }

    public function test_field_agent_and_support_agent_have_view_only_access(): void
    {
        $fieldAgent = $this->makeUser('field_agent');
        $supportAgent = $this->makeUser('support_agent');
        $hospital = $this->makeHospital();

        // Field Agent
        $this->actingAs($fieldAgent);
        $this->get(HospitalResource::getUrl('index'))->assertOk();
        $this->get(HospitalResource::getUrl('view', ['record' => $hospital]))->assertOk();
        $this->get(HospitalResource::getUrl('create'))->assertForbidden();
        $this->get(HospitalResource::getUrl('edit', ['record' => $hospital]))->assertForbidden();

        // Support Agent
        $this->actingAs($supportAgent);
        $this->get(HospitalResource::getUrl('index'))->assertOk();
        $this->get(HospitalResource::getUrl('view', ['record' => $hospital]))->assertOk();
        $this->get(HospitalResource::getUrl('create'))->assertForbidden();
        $this->get(HospitalResource::getUrl('edit', ['record' => $hospital]))->assertForbidden();
    }

    public function test_unauthorized_roles_cannot_access_hospital_resource(): void
    {
        $contentEditor = $this->makeUser('content_editor');
        $medicalReviewer = $this->makeUser('medical_reviewer');

        $this->actingAs($contentEditor);
        $this->get(HospitalResource::getUrl('index'))->assertForbidden();

        $this->actingAs($medicalReviewer);
        $this->get(HospitalResource::getUrl('index'))->assertForbidden();
    }

    public function test_hospital_creation_and_automatic_capability_matrix_initialization(): void
    {
        $manager = $this->makeUser('hospital_manager');

        Livewire::actingAs($manager)
            ->test(CreateHospital::class)
            ->fillForm([
                'name_bn' => 'নতুন ক্যান্সার হাসপাতাল',
                'name_en' => 'New Cancer Hospital',
                'slug' => 'new-cancer-hospital',
                'type' => HospitalType::Private->value,
                'district_id' => $this->district->id,
                'address_bn' => 'ধানমন্ডি, ঢাকা',
                'phone' => '০১৭০০-০০০০০০',
                'established_year' => 2015,
                'bed_count' => 150,
                'oncologist_count' => 12,
                'outdoor_fee' => 800,
                'emergency_24h' => true,
                'annual_patients' => '২০,০০০+',
                'description_bn' => 'আধুনিক বেসরকারি ক্যান্সার হাসপাতাল।',
                'status' => HospitalStatus::Published->value,
                'last_verified_at' => now()->toDateString(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $hospital = Hospital::where('slug', 'new-cancer-hospital')->first();
        $this->assertNotNull($hospital);

        // Verify all 11 capabilities were automatically initialized for this hospital
        $this->assertCount(11, $hospital->capabilities);
        $this->assertEquals(11, $hospital->capabilities()->where('status', HospitalCapabilityStatus::NotAvailable)->count());
    }

    public function test_hospital_capability_matrix_editing_via_relation_manager(): void
    {
        $manager = $this->makeUser('hospital_manager');
        $hospital = $this->makeHospital();
        app(HospitalService::class)->initializeCapabilities($hospital);

        $capabilityRecord = $hospital->capabilities()->first();
        $this->assertNotNull($capabilityRecord);

        Livewire::actingAs($manager)
            ->test(CapabilitiesRelationManager::class, [
                'ownerRecord' => $hospital,
                'pageClass' => EditHospital::class,
            ])
            ->callTableAction('edit', $capabilityRecord, data: [
                'status' => HospitalCapabilityStatus::Available->value,
                'detail_bn' => '৩টি অত্যাধুনিক লিনিয়ার এক্সিলারেটর সচল',
                'machine_count' => 3,
                'last_checked_at' => now()->toDateString(),
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('hospital_capabilities', [
            'id' => $capabilityRecord->id,
            'status' => HospitalCapabilityStatus::Available->value,
            'machine_count' => 3,
            'detail_bn' => '৩টি অত্যাধুনিক লিনিয়ার এক্সিলারেটর সচল',
        ]);
    }

    public function test_hospital_wait_times_relation_manager_crud(): void
    {
        $manager = $this->makeUser('hospital_manager');
        $hospital = $this->makeHospital();

        Livewire::actingAs($manager)
            ->test(WaitTimesRelationManager::class, [
                'ownerRecord' => $hospital,
                'pageClass' => EditHospital::class,
            ])
            ->callTableAction('create', data: [
                'service_key' => 'radiotherapy_start',
                'label_bn' => 'রেডিওথেরাপির সিরিয়াল পাওয়া',
                'min_weeks' => 4,
                'max_weeks' => 8,
                'severity' => HospitalWaitTimeSeverity::Long->value,
                'sort_order' => 1,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('hospital_wait_times', [
            'hospital_id' => $hospital->id,
            'service_key' => 'radiotherapy_start',
            'min_weeks' => 4,
            'max_weeks' => 8,
        ]);
    }

    public function test_hospital_costs_relation_manager_crud(): void
    {
        $manager = $this->makeUser('hospital_manager');
        $hospital = $this->makeHospital();

        Livewire::actingAs($manager)
            ->test(CostsRelationManager::class, [
                'ownerRecord' => $hospital,
                'pageClass' => EditHospital::class,
            ])
            ->callTableAction('create', data: [
                'service_key' => 'chemo_per_cycle',
                'label_bn' => 'কেমোথেরাপি (প্রতি সাইকেল সরকারি চার্জ)',
                'min_amount' => 500,
                'max_amount' => 2000,
                'note_bn' => 'ওষুধের খরচ আলাদা',
                'sort_order' => 1,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('hospital_costs', [
            'hospital_id' => $hospital->id,
            'service_key' => 'chemo_per_cycle',
            'min_amount' => 500,
            'max_amount' => 2000,
        ]);
    }

    public function test_hospital_prep_info_relation_manager_crud(): void
    {
        $manager = $this->makeUser('hospital_manager');
        $hospital = $this->makeHospital();

        Livewire::actingAs($manager)
            ->test(PrepInfoRelationManager::class, [
                'ownerRecord' => $hospital,
                'pageClass' => EditHospital::class,
            ])
            ->callTableAction('create', data: [
                'key' => HospitalPrepKey::BloodBank->value,
                'title_bn' => 'ব্লাড ব্যাংক ও ডোনার প্রস্তুতি',
                'description_bn' => '২ জন রক্তদাতা সাথে রাখা বাধ্যতামূলক।',
                'flag_text_bn' => 'ডোনার সাথে থাকা জরুরি',
                'flag_type' => HospitalFlagType::Warning->value,
                'sort_order' => 1,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('hospital_prep_info', [
            'hospital_id' => $hospital->id,
            'key' => HospitalPrepKey::BloodBank->value,
            'flag_text_bn' => 'ডোনার সাথে থাকা জরুরি',
        ]);
    }

    public function test_hospital_practical_info_relation_manager_crud(): void
    {
        $manager = $this->makeUser('hospital_manager');
        $hospital = $this->makeHospital();

        Livewire::actingAs($manager)
            ->test(PracticalInfoRelationManager::class, [
                'ownerRecord' => $hospital,
                'pageClass' => EditHospital::class,
            ])
            ->callTableAction('create', data: [
                'key' => HospitalPracticalKey::Documents->value,
                'title_bn' => 'প্রথম দিনে যা যা সাথে আনবেন',
                'description_bn' => 'এনআইডি ও বায়োপসি রিপোর্ট সঙ্গে আনুন।',
                'icon' => 'file-text',
                'sort_order' => 1,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('hospital_practical_info', [
            'hospital_id' => $hospital->id,
            'key' => HospitalPracticalKey::Documents->value,
            'title_bn' => 'প্রথম দিনে যা যা সাথে আনবেন',
        ]);
    }

    public function test_hospital_videos_relation_manager_crud(): void
    {
        $manager = $this->makeUser('hospital_manager');
        $hospital = $this->makeHospital();

        Livewire::actingAs($manager)
            ->test(VideosRelationManager::class, [
                'ownerRecord' => $hospital,
                'pageClass' => EditHospital::class,
            ])
            ->callTableAction('create', data: [
                'title_bn' => 'জাতীয় ক্যান্সার হাসপাতালে প্রথম দিন',
                'video_url' => 'https://www.youtube.com/watch?v=demo123',
                'platform' => 'youtube',
                'duration_seconds' => 180,
                'produced_by' => 'CancerCare Bangladesh',
                'sort_order' => 1,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('hospital_videos', [
            'hospital_id' => $hospital->id,
            'video_url' => 'https://www.youtube.com/watch?v=demo123',
        ]);
    }

    public function test_hospital_doctors_relation_manager_attach_and_edit(): void
    {
        $manager = $this->makeUser('hospital_manager');
        $hospital = $this->makeHospital();
        $doctor = $this->makeDoctor();

        Livewire::actingAs($manager)
            ->test(DoctorsRelationManager::class, [
                'ownerRecord' => $hospital,
                'pageClass' => EditHospital::class,
            ])
            ->callTableAction('attach', data: [
                'recordId' => $doctor->id,
                'schedule_note_bn' => 'রবি ও মঙ্গল সকাল ৯টা',
                'sort_order' => 1,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('hospital_doctor', [
            'hospital_id' => $hospital->id,
            'doctor_id' => $doctor->id,
            'schedule_note_bn' => 'রবি ও মঙ্গল সকাল ৯টা',
        ]);
    }

    public function test_hospital_experience_summary_calculation(): void
    {
        $fieldAgent = $this->makeUser('field_agent');
        $hospital = $this->makeHospital();
        $question = HospitalExperienceQuestion::first();
        $this->assertNotNull($question);

        // Add 30 responses (24 yes, 6 no -> 80% positive)
        for ($i = 0; $i < 24; $i++) {
            HospitalExperienceResponse::create([
                'hospital_id' => $hospital->id,
                'question_id' => $question->id,
                'answer' => true,
                'collected_by' => $fieldAgent->id,
                'source' => 'field_hospital',
                'collected_at' => now(),
            ]);
        }
        for ($i = 0; $i < 6; $i++) {
            HospitalExperienceResponse::create([
                'hospital_id' => $hospital->id,
                'question_id' => $question->id,
                'answer' => false,
                'collected_by' => $fieldAgent->id,
                'source' => 'field_hospital',
                'collected_at' => now(),
            ]);
        }

        app(HospitalService::class)->recalculateExperienceSummary($hospital);

        $this->assertDatabaseHas('hospital_experience_summaries', [
            'hospital_id' => $hospital->id,
            'question_id' => $question->id,
            'yes_count' => 24,
            'total_count' => 30,
            'percentage' => 80.00,
            'is_published' => true,
        ]);
    }
}
