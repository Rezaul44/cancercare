<?php

namespace Tests\Feature;

use App\Enums\PatientCaseAccountType;
use App\Enums\PatientCaseGender;
use App\Enums\PatientCaseStatus;
use App\Enums\PatientCaseVerificationStatus;
use App\Enums\PatientCaseVerificationStep;
use App\Models\CancerType;
use App\Models\District;
use App\Models\Division;
use App\Models\Hospital;
use App\Models\PatientCase;
use App\Models\PatientCaseAccount;
use App\Models\PatientCaseCost;
use App\Models\PatientCaseDocument;
use App\Models\PatientCaseVerification;
use App\Models\User;
use App\Services\VerificationService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PatientSupportPublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected CancerType $breastCancer;
    protected CancerType $bloodCancer;
    protected District $districtDhaka;
    protected District $districtBogra;
    protected Hospital $hospital;
    protected User $staffUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $division = Division::create([
            'name_bn' => 'ঢাকা',
            'name_en' => 'Dhaka',
            'slug' => 'dhaka',
        ]);

        $this->districtDhaka = District::create([
            'division_id' => $division->id,
            'name_bn' => 'ঢাকা',
            'name_en' => 'Dhaka',
            'slug' => 'dhaka-dist',
            'distance_tier' => 'local',
            'has_cancer_center' => true,
        ]);

        $this->districtBogra = District::create([
            'division_id' => $division->id,
            'name_bn' => 'বগুড়া',
            'name_en' => 'Bogra',
            'slug' => 'bogra',
            'distance_tier' => 'far',
            'has_cancer_center' => false,
        ]);

        $this->breastCancer = CancerType::create([
            'name_bn' => 'স্তন ক্যান্সার',
            'name_en' => 'Breast Cancer',
            'slug' => 'breast-cancer',
            'icon' => 'ribbon',
            'color_key' => 'pink',
            'short_description_bn' => 'বিবরণ',
            'gender_bias' => 'female',
            'is_common' => true,
            'guide_published' => true,
            'sort_order' => 1,
        ]);

        $this->bloodCancer = CancerType::create([
            'name_bn' => 'রক্তের ক্যান্সার',
            'name_en' => 'Blood Cancer',
            'slug' => 'blood-cancer',
            'icon' => 'droplet',
            'color_key' => 'red',
            'short_description_bn' => 'বিবরণ',
            'is_common' => true,
            'guide_published' => true,
            'sort_order' => 2,
        ]);

        $this->hospital = Hospital::create([
            'name_bn' => 'জাতীয় ক্যান্সার গবেষণা ইনস্টিটিউট ও হাসপাতাল',
            'name_en' => 'NICRH',
            'slug' => 'nicrh',
            'type' => 'govt',
            'address_bn' => 'মহাখালী, ঢাকা',
            'phone' => '02-9898989',
            'description_bn' => 'ক্যান্সার চিকিৎসার প্রধান জাতীয় হাসপাতাল।',
            'district_id' => $this->districtDhaka->id,
            'status' => 'published',
        ]);

        $this->staffUser = User::factory()->create([
            'email' => 'staff@cancercare.test',
        ]);
        $this->staffUser->assignRole('super_admin');
    }

    public function test_patients_index_displays_published_cases_and_ccb_role_band(): void
    {
        // 1. Published case (should be visible)
        $publishedCase = PatientCase::create([
            'real_name' => 'মোছা. সালমা খাতুন',
            'display_name_bn' => 'সালমা বেগম',
            'age' => 42,
            'gender' => PatientCaseGender::Female,
            'cancer_type_id' => $this->breastCancer->id,
            'stage' => 'স্টেজ ৩',
            'district_id' => $this->districtBogra->id,
            'hospital_id' => $this->hospital->id,
            'story_bn' => 'সালমা বেগমের চিকিৎসার গল্প।',
            'amount_needed' => 185000,
            'consent_form_path' => 'consent.pdf',
            'status' => PatientCaseStatus::Published,
            'published_at' => now()->subDay(),
            'expires_at' => now()->addDays(29),
            'verified_at' => now()->subDay(),
            'created_by' => $this->staffUser->id,
            'verified_by' => $this->staffUser->id,
        ]);

        // 2. Draft case (should NOT be visible)
        $draftCase = PatientCase::create([
            'real_name' => 'খসড়া রোগী',
            'display_name_bn' => 'খসড়া রোগী',
            'age' => 30,
            'gender' => PatientCaseGender::Male,
            'cancer_type_id' => $this->bloodCancer->id,
            'district_id' => $this->districtDhaka->id,
            'story_bn' => 'খসড়া গল্প',
            'amount_needed' => 50000,
            'status' => PatientCaseStatus::Draft,
            'created_by' => $this->staffUser->id,
        ]);

        // 3. Expired case (should NOT be visible)
        $expiredCase = PatientCase::create([
            'real_name' => 'মেয়াদোত্তীর্ণ রোগী',
            'display_name_bn' => 'মেয়াদোত্তীর্ণ রোগী',
            'age' => 50,
            'gender' => PatientCaseGender::Male,
            'cancer_type_id' => $this->breastCancer->id,
            'district_id' => $this->districtDhaka->id,
            'story_bn' => 'মেয়াদোত্তীর্ণ গল্প',
            'amount_needed' => 80000,
            'status' => PatientCaseStatus::Expired,
            'published_at' => now()->subDays(35),
            'expires_at' => now()->subDays(5),
            'created_by' => $this->staffUser->id,
        ]);

        $response = $this->get(route('patients.index'));

        $response->assertStatus(200);
        $response->assertSee('যাচাই করা রোগী, সরাসরি সহায়তা');
        $response->assertSee('CCB-র ভূমিকা কী');
        $response->assertSee('CCB কোনো টাকা সংগ্রহ করে না, ধরে রাখে না, বিতরণও করে না।');
        $response->assertSee('প্রতারণা থেকে সাবধান');
        $response->assertSee('সালমা বেগম');
        $response->assertSee('৳185,000');
        $response->assertDontSee('খসড়া রোগী');
        $response->assertDontSee('মেয়াদোত্তীর্ণ রোগী');
    }

    public function test_patients_index_sorting_and_filtering(): void
    {
        $case1 = PatientCase::create([
            'real_name' => 'রোগী এক',
            'display_name_bn' => 'রোগী এক',
            'age' => 40,
            'gender' => PatientCaseGender::Female,
            'cancer_type_id' => $this->breastCancer->id,
            'district_id' => $this->districtDhaka->id,
            'story_bn' => 'বিবরণ ১',
            'amount_needed' => 100000,
            'consent_form_path' => 'consent1.pdf',
            'status' => PatientCaseStatus::Published,
            'published_at' => now()->subDays(10),
            'expires_at' => now()->addDays(5), // urgent
            'verified_at' => now()->subDays(10),
            'created_by' => $this->staffUser->id,
        ]);

        $case2 = PatientCase::create([
            'real_name' => 'রোগী দুই',
            'display_name_bn' => 'রোগী দুই',
            'age' => 35,
            'gender' => PatientCaseGender::Male,
            'cancer_type_id' => $this->bloodCancer->id,
            'district_id' => $this->districtBogra->id,
            'story_bn' => 'বিবরণ ২',
            'amount_needed' => 300000, // high amount
            'consent_form_path' => 'consent2.pdf',
            'status' => PatientCaseStatus::Published,
            'published_at' => now()->subDay(),
            'expires_at' => now()->addDays(29),
            'verified_at' => now()->subDay(), // recent
            'created_by' => $this->staffUser->id,
        ]);

        // Sort by recent
        $resRecent = $this->get(route('patients.index', ['sort' => 'recent']));
        $resRecent->assertStatus(200);
        $resRecent->assertSeeInOrder(['রোগী দুই', 'রোগী এক']);

        // Sort by urgent (least days left)
        $resUrgent = $this->get(route('patients.index', ['sort' => 'urgent']));
        $resUrgent->assertStatus(200);
        $resUrgent->assertSeeInOrder(['রোগী এক', 'রোগী দুই']);

        // Sort by amount desc
        $resAmountDesc = $this->get(route('patients.index', ['sort' => 'amount_desc']));
        $resAmountDesc->assertStatus(200);
        $resAmountDesc->assertSeeInOrder(['রোগী দুই', 'রোগী এক']);

        // Filter by cancer type slug
        $resFilter = $this->get(route('patients.index', ['cancer' => 'breast-cancer']));
        $resFilter->assertStatus(200);
        $resFilter->assertSee('রোগী এক');
        $resFilter->assertDontSee('রোগী দুই');
    }

    public function test_patient_show_displays_details_and_recipient_accounts(): void
    {
        $case = PatientCase::create([
            'real_name' => 'সুলতানা রাজিয়া',
            'display_name_bn' => 'সুলতানা বেগম',
            'age' => 45,
            'gender' => PatientCaseGender::Female,
            'cancer_type_id' => $this->breastCancer->id,
            'stage' => 'স্টেজ ২',
            'district_id' => $this->districtDhaka->id,
            'hospital_id' => $this->hospital->id,
            'treating_doctor_name' => 'ডা. মোফাজ্জল হোসেন',
            'story_bn' => 'আমার চিকিৎসার জন্য সবার আন্তরিক সহায়তা কামনা করছি।',
            'amount_needed' => 120000,
            'consent_form_path' => 'consent.pdf',
            'status' => PatientCaseStatus::Published,
            'published_at' => now()->subDays(2),
            'expires_at' => now()->addDays(28),
            'verified_at' => now()->subDays(2),
            'created_by' => $this->staffUser->id,
        ]);

        // Verifications
        foreach (VerificationService::REQUIRED_STEPS as $step) {
            PatientCaseVerification::create([
                'patient_case_id' => $case->id,
                'step' => $step,
                'status' => PatientCaseVerificationStatus::Done,
                'note_bn' => "যাচাই নোট {$step}",
                'completed_by' => $this->staffUser->id,
                'completed_at' => now()->subDays(2),
            ]);
        }

        // Costs
        PatientCaseCost::create([
            'patient_case_id' => $case->id,
            'item_bn' => 'কেমোথেরাপি ৬ সাইকেল',
            'amount' => 80000,
            'sort_order' => 1,
        ]);
        PatientCaseCost::create([
            'patient_case_id' => $case->id,
            'item_bn' => 'সার্জারি খরচ',
            'amount' => 40000,
            'sort_order' => 2,
        ]);

        // Accounts
        PatientCaseAccount::create([
            'patient_case_id' => $case->id,
            'type' => PatientCaseAccountType::Bkash,
            'account_number' => '01712-345678',
            'account_name' => 'সুলতানা রাজিয়া',
            'name_verified' => true,
            'is_active' => true,
        ]);
        PatientCaseAccount::create([
            'patient_case_id' => $case->id,
            'type' => PatientCaseAccountType::Bank,
            'account_number' => '123.456.789',
            'account_name' => 'সুলতানা রাজিয়া',
            'bank_name' => 'সোনালী ব্যাংক পিএলসি',
            'branch' => 'মিরপুর শাখা',
            'name_verified' => true,
            'is_active' => true,
        ]);

        // Documents
        PatientCaseDocument::create([
            'patient_case_id' => $case->id,
            'type' => 'biopsy',
            'file_path' => 'doc/biopsy.pdf',
            'is_public' => true,
            'redacted' => true,
        ]);

        $response = $this->get(route('patients.show', $case->case_code));

        $response->assertStatus(200);
        $response->assertSee('সুলতানা বেগম');
        $response->assertSee('৳120,000');
        $response->assertSee('আমার চিকিৎসার জন্য সবার আন্তরিক সহায়তা কামনা করছি।');
        $response->assertSee('সরাসরি সাহায্য পাঠান');
        $response->assertSee('01712-345678');
        $response->assertSee('123.456.789');
        $response->assertSee('সোনালী ব্যাংক পিএলসি');
        $response->assertSee('কেমোথেরাপি ৬ সাইকেল');
        $response->assertSee('সার্জারি খরচ');
        $response->assertSee('যাচাইকরণের ধাপসমূহ');
        $response->assertSee('CCB কখনো নিজের নম্বরে অনুদান চায় না');
    }

    public function test_patient_show_returns_404_for_draft_case(): void
    {
        $draftCase = PatientCase::create([
            'real_name' => 'অপ্রকাশিত রোগী',
            'display_name_bn' => 'অপ্রকাশিত',
            'age' => 28,
            'gender' => PatientCaseGender::Male,
            'cancer_type_id' => $this->breastCancer->id,
            'district_id' => $this->districtDhaka->id,
            'story_bn' => 'গল্প',
            'amount_needed' => 40000,
            'status' => PatientCaseStatus::Draft,
            'created_by' => $this->staffUser->id,
        ]);

        $response = $this->get(route('patients.show', $draftCase->case_code));
        $response->assertStatus(404);
    }

    public function test_apply_for_support_page_renders_guide_content(): void
    {
        $response = $this->get(route('patients.apply'));

        $response->assertStatus(200);
        $response->assertSee('সহায়তার জন্য কীভাবে আবেদন করবেন');
        $response->assertSee('CCB-র সহায়তা কীভাবে কাজ করে');
        $response->assertSee('কারা আবেদন করতে পারবেন');
        $response->assertSee('যা যা সাথে রাখতে হবে');
        $response->assertSee('যেভাবে যাচাই সম্পন্ন হয়');
        $response->assertSee('০৯৬১১-৭৭৭৮৮৮');
        $response->assertSee('সীমিত সামর্থ্যের কথা খোলাখুলি বলি');
    }

    public function test_child_patient_strictly_hides_photo_and_shows_child_protection_badge(): void
    {
        $childCase = PatientCase::create([
            'real_name' => 'শিশু তানভীর',
            'display_name_bn' => 'শিশু তানভীর',
            'age' => 6,
            'gender' => PatientCaseGender::Male,
            'cancer_type_id' => $this->bloodCancer->id,
            'district_id' => $this->districtDhaka->id,
            'story_bn' => 'শিশু তানভীরের চিকিৎসা চলছে।',
            'amount_needed' => 250000,
            'photo_path' => 'photos/child_tanvir.jpg',
            'show_photo' => true, // will be forced to false by observer
            'consent_form_path' => 'consent.pdf',
            'status' => PatientCaseStatus::Published,
            'published_at' => now(),
            'expires_at' => now()->addDays(30),
            'verified_at' => now(),
            'created_by' => $this->staffUser->id,
        ]);

        $this->assertFalse($childCase->show_photo);

        $resIndex = $this->get(route('patients.index'));
        $resIndex->assertStatus(200);
        $resIndex->assertSee('শিশু তানভীর');
        $resIndex->assertSee('শিশু — সুরক্ষা নীতিতে ছবি লুকানো');

        $resShow = $this->get(route('patients.show', $childCase->case_code));
        $resShow->assertStatus(200);
        $resShow->assertSee('শিশু তানভীর');
        // Initial avatar is rendered instead of img
        $resShow->assertSee('শি');
    }
}
