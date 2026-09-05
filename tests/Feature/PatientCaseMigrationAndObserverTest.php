<?php

namespace Tests\Feature;

use App\Enums\PatientCaseAccountType;
use App\Enums\PatientCaseAnonymity;
use App\Enums\PatientCaseDocumentType;
use App\Enums\PatientCaseGender;
use App\Enums\PatientCaseReportStatus;
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
use App\Models\PatientCaseReport;
use App\Models\PatientCaseUpdate;
use App\Models\PatientCaseVerification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PatientCaseMigrationAndObserverTest extends TestCase
{
    use RefreshDatabase;

    protected CancerType $cancerType;
    protected District $district;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

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

        $this->user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@cancercare.test',
            'password' => bcrypt('secret123'),
            'user_type' => 'staff',
            'status' => 'active',
        ]);
    }

    public function test_patient_case_tables_and_columns_exist_and_forbidden_columns_are_absent(): void
    {
        $this->assertTrue(Schema::hasTable('patient_cases'));
        $this->assertTrue(Schema::hasTable('patient_case_verifications'));
        $this->assertTrue(Schema::hasTable('patient_case_documents'));
        $this->assertTrue(Schema::hasTable('patient_case_costs'));
        $this->assertTrue(Schema::hasTable('patient_case_accounts'));
        $this->assertTrue(Schema::hasTable('patient_case_updates'));
        $this->assertTrue(Schema::hasTable('patient_case_reports'));

        // Crucial schema integrity check: CCB does NOT hold funds, so these columns MUST NOT exist
        $this->assertFalse(Schema::hasColumn('patient_cases', 'amount_raised'));
        $this->assertFalse(Schema::hasColumn('patient_cases', 'donation_count'));
        $this->assertFalse(Schema::hasColumn('patient_cases', 'progress_percent'));

        // Check required fields exist
        $this->assertTrue(Schema::hasColumns('patient_cases', [
            'id', 'case_code', 'real_name', 'display_name_bn', 'age', 'gender',
            'cancer_type_id', 'stage', 'district_id', 'hospital_id', 'treating_doctor_name',
            'story_bn', 'amount_needed', 'photo_path', 'show_photo', 'anonymity_level',
            'consent_form_path', 'consent_signed_at', 'status', 'verified_at',
            'published_at', 'expires_at', 'created_by', 'verified_by'
        ]));
    }

    public function test_patient_case_code_is_automatically_generated_in_expected_format(): void
    {
        $year = Carbon::now()->format('Y');

        $case1 = PatientCase::create([
            'real_name' => 'রহিমা খাতুন',
            'display_name_bn' => 'রহিমা বেগম',
            'age' => 45,
            'gender' => PatientCaseGender::Female,
            'cancer_type_id' => $this->cancerType->id,
            'district_id' => $this->district->id,
            'story_bn' => 'রোগীর চিকিৎসার জন্য আর্থিক সহায়তা প্রয়োজন।',
            'amount_needed' => 150000,
            'status' => PatientCaseStatus::Draft,
            'created_by' => $this->user->id,
        ]);

        $this->assertEquals("CCB-{$year}-0001", $case1->case_code);

        $case2 = PatientCase::create([
            'real_name' => 'আব্দুল করিম',
            'display_name_bn' => 'করিম সাহেব',
            'age' => 52,
            'gender' => PatientCaseGender::Male,
            'cancer_type_id' => $this->cancerType->id,
            'district_id' => $this->district->id,
            'story_bn' => 'কেমোথেরাপির জন্য সাহায্য প্রয়োজন।',
            'amount_needed' => 200000,
            'status' => PatientCaseStatus::Draft,
            'created_by' => $this->user->id,
        ]);

        $this->assertEquals("CCB-{$year}-0002", $case2->case_code);
    }

    public function test_child_photo_protection_rule_forces_show_photo_false_when_age_under_18(): void
    {
        // Child case with show_photo = true passed in
        $childCase = PatientCase::create([
            'real_name' => 'শিশু রোগী',
            'display_name_bn' => 'তানভীর',
            'age' => 9,
            'gender' => PatientCaseGender::Male,
            'cancer_type_id' => $this->cancerType->id,
            'district_id' => $this->district->id,
            'story_bn' => 'লিউকেমিয়ায় আক্রান্ত শিশুর চিকিৎসা সহায়তা।',
            'amount_needed' => 300000,
            'show_photo' => true, // Attempted to show photo
            'status' => PatientCaseStatus::Draft,
            'created_by' => $this->user->id,
        ]);

        // Forced to false by observer
        $this->assertFalse($childCase->show_photo);

        // Attempting to update show_photo to true
        $childCase->show_photo = true;
        $childCase->save();

        $childCase->refresh();
        $this->assertFalse($childCase->show_photo);

        // Adult case allows show_photo = true
        $adultCase = PatientCase::create([
            'real_name' => 'সাবালক রোগী',
            'display_name_bn' => 'আফরোজা',
            'age' => 34,
            'gender' => PatientCaseGender::Female,
            'cancer_type_id' => $this->cancerType->id,
            'district_id' => $this->district->id,
            'story_bn' => 'চিকিৎসা সহায়তা গল্প।',
            'amount_needed' => 120000,
            'show_photo' => true,
            'status' => PatientCaseStatus::Draft,
            'created_by' => $this->user->id,
        ]);

        $this->assertTrue($adultCase->show_photo);
    }

    public function test_publishing_lifecycle_sets_expires_at_to_thirty_days_later(): void
    {
        $now = Carbon::create(2026, 9, 2, 10, 0, 0);
        Carbon::setTestNow($now);

        $case = PatientCase::create([
            'real_name' => 'কামাল উদ্দিন',
            'display_name_bn' => 'কামাল হোসেন',
            'age' => 48,
            'gender' => PatientCaseGender::Male,
            'cancer_type_id' => $this->cancerType->id,
            'district_id' => $this->district->id,
            'story_bn' => 'রেডিওথেরাপির খরচের জন্য অনুদান।',
            'amount_needed' => 80000,
            'status' => PatientCaseStatus::Verifying,
            'created_by' => $this->user->id,
        ]);

        $this->assertNull($case->published_at);
        $this->assertNull($case->expires_at);

        // Publish the case
        $case->update([
            'status' => PatientCaseStatus::Published,
            'verified_by' => $this->user->id,
            'verified_at' => $now,
        ]);

        $case->refresh();

        $this->assertEquals(PatientCaseStatus::Published, $case->status);
        $this->assertNotNull($case->published_at);
        $this->assertEquals('2026-09-02 10:00:00', $case->published_at->format('Y-m-d H:i:s'));
        $this->assertNotNull($case->expires_at);
        $this->assertEquals('2026-10-02 10:00:00', $case->expires_at->format('Y-m-d H:i:s'));
        $this->assertEquals(30, (int) $case->published_at->diffInDays($case->expires_at));

        Carbon::setTestNow(); // Reset time
    }

    public function test_patient_case_relationships_across_all_child_tables(): void
    {
        $case = PatientCase::create([
            'real_name' => 'মমতাজ বেগম',
            'display_name_bn' => 'মমতাজ',
            'age' => 42,
            'gender' => PatientCaseGender::Female,
            'cancer_type_id' => $this->cancerType->id,
            'district_id' => $this->district->id,
            'story_bn' => 'কেমোথেরাপির জন্য সাহায্য।',
            'amount_needed' => 150000,
            'status' => PatientCaseStatus::Draft,
            'created_by' => $this->user->id,
        ]);

        // Verification step
        $verification = PatientCaseVerification::create([
            'patient_case_id' => $case->id,
            'step' => PatientCaseVerificationStep::Documents,
            'status' => PatientCaseVerificationStatus::Done,
            'note_bn' => 'বায়োপসি রিপোর্ট ও প্রাক্কলন যাচাই করা হয়েছে।',
            'completed_by' => $this->user->id,
            'completed_at' => now(),
        ]);

        // Document
        $document = PatientCaseDocument::create([
            'patient_case_id' => $case->id,
            'type' => PatientCaseDocumentType::Biopsy,
            'file_path' => 'documents/biopsy_sample.pdf',
            'is_public' => true,
            'redacted' => true,
            'uploaded_by' => $this->user->id,
            'uploaded_at' => now(),
        ]);

        // Cost Breakdown
        $cost = PatientCaseCost::create([
            'patient_case_id' => $case->id,
            'item_bn' => '৬টি কেমোথেরাপি সাইকেল',
            'amount' => 90000,
            'sort_order' => 1,
        ]);

        // Account
        $account = PatientCaseAccount::create([
            'patient_case_id' => $case->id,
            'type' => PatientCaseAccountType::Bkash,
            'account_number' => '01700000000',
            'account_name' => 'মমতাজ বেগম',
            'name_verified' => true,
            'is_active' => true,
        ]);

        // Update
        $update = PatientCaseUpdate::create([
            'patient_case_id' => $case->id,
            'note_bn' => 'প্রথম ৩টি সাইকেল সফলভাবে সম্পন্ন হয়েছে।',
            'update_date' => now(),
            'created_by' => $this->user->id,
            'is_public' => true,
        ]);

        // Report
        $report = PatientCaseReport::create([
            'patient_case_id' => $case->id,
            'reporter_phone' => '01800000000',
            'reason' => 'অ্যাকাউন্ট নম্বরে ভুল আছে কিনা যাচাই অনুরোধ',
            'status' => PatientCaseReportStatus::New,
        ]);

        // Assert relationships
        $this->assertCount(1, $case->verifications);
        $this->assertEquals(PatientCaseVerificationStep::Documents, $case->verifications->first()->step);

        $this->assertCount(1, $case->documents);
        $this->assertEquals(PatientCaseDocumentType::Biopsy, $case->documents->first()->type);

        $this->assertCount(1, $case->costs);
        $this->assertEquals(90000, $case->costs->first()->amount);

        $this->assertCount(1, $case->accounts);
        $this->assertEquals(PatientCaseAccountType::Bkash, $case->accounts->first()->type);

        $this->assertCount(1, $case->updates);
        $this->assertEquals('প্রথম ৩টি সাইকেল সফলভাবে সম্পন্ন হয়েছে।', $case->updates->first()->note_bn);

        $this->assertCount(1, $case->reports);
        $this->assertEquals(PatientCaseReportStatus::New, $case->reports->first()->status);

        // Test cascade deletion
        $caseId = $case->id;
        $case->delete(); // Soft delete

        $this->assertSoftDeleted('patient_cases', ['id' => $caseId]);

        $case->forceDelete(); // Hard delete cascades to children
        $this->assertDatabaseMissing('patient_case_verifications', ['patient_case_id' => $caseId]);
        $this->assertDatabaseMissing('patient_case_documents', ['patient_case_id' => $caseId]);
        $this->assertDatabaseMissing('patient_case_costs', ['patient_case_id' => $caseId]);
        $this->assertDatabaseMissing('patient_case_accounts', ['patient_case_id' => $caseId]);
        $this->assertDatabaseMissing('patient_case_updates', ['patient_case_id' => $caseId]);
        $this->assertDatabaseMissing('patient_case_reports', ['patient_case_id' => $caseId]);
    }
}
