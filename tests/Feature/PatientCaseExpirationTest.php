<?php

namespace Tests\Feature;

use App\Enums\PatientCaseGender;
use App\Enums\PatientCaseStatus;
use App\Jobs\ExpirePatientCases;
use App\Jobs\NotifyCaseExpiring;
use App\Models\CancerType;
use App\Models\District;
use App\Models\Division;
use App\Models\PatientCase;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PatientCaseExpirationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected CancerType $cancerType;
    protected District $district;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->adminUser = User::factory()->create([
            'name' => 'Admin Staff',
            'email' => 'admin@cancercare.test',
        ]);
        $this->adminUser->assignRole('super_admin');

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
            'short_description_bn' => 'বিবরণ',
            'gender_bias' => 'female',
            'is_common' => true,
            'guide_published' => true,
            'sort_order' => 1,
        ]);
    }

    public function test_expire_patient_cases_job_expires_past_cases_only(): void
    {
        // 1. Case whose expiration passed 1 hour ago
        $expiredCase = PatientCase::create([
            'real_name' => 'মেয়াদ শেষ রোগী',
            'display_name_bn' => 'মেয়াদ শেষ রোগী',
            'age' => 45,
            'gender' => PatientCaseGender::Female,
            'cancer_type_id' => $this->cancerType->id,
            'district_id' => $this->district->id,
            'story_bn' => 'বিবরণ',
            'amount_needed' => 100000,
            'status' => PatientCaseStatus::Published,
            'published_at' => Carbon::now()->subDays(31),
            'expires_at' => Carbon::now()->subHour(),
            'created_by' => $this->adminUser->id,
        ]);

        // 2. Case with 10 days remaining (should stay published)
        $activeCase = PatientCase::create([
            'real_name' => 'সক্রিয় রোগী',
            'display_name_bn' => 'সক্রিয় রোগী',
            'age' => 38,
            'gender' => PatientCaseGender::Male,
            'cancer_type_id' => $this->cancerType->id,
            'district_id' => $this->district->id,
            'story_bn' => 'বিবরণ',
            'amount_needed' => 150000,
            'status' => PatientCaseStatus::Published,
            'published_at' => Carbon::now()->subDays(20),
            'expires_at' => Carbon::now()->addDays(10),
            'created_by' => $this->adminUser->id,
        ]);

        // Execute Job
        $job = new ExpirePatientCases();
        $count = $job->handle();

        $this->assertEquals(1, $count);

        $expiredCase->refresh();
        $activeCase->refresh();

        $this->assertEquals(PatientCaseStatus::Expired, $expiredCase->status);
        $this->assertEquals(PatientCaseStatus::Published, $activeCase->status);
    }

    public function test_notify_case_expiring_job_alerts_staff_for_cases_with_5_days_left(): void
    {
        // 1. Case expiring in 3 days (within 5 days threshold)
        $expiringCase = PatientCase::create([
            'real_name' => 'মেয়াদ আসন্ন রোগী',
            'display_name_bn' => 'মেয়াদ আসন্ন',
            'age' => 50,
            'gender' => PatientCaseGender::Female,
            'cancer_type_id' => $this->cancerType->id,
            'district_id' => $this->district->id,
            'story_bn' => 'বিবরণ',
            'amount_needed' => 120000,
            'status' => PatientCaseStatus::Published,
            'published_at' => Carbon::now()->subDays(27),
            'expires_at' => Carbon::now()->addDays(3),
            'created_by' => $this->adminUser->id,
        ]);

        // 2. Case expiring in 20 days (outside threshold)
        $safeCase = PatientCase::create([
            'real_name' => 'নিরাপদ রোগী',
            'display_name_bn' => 'নিরাপদ',
            'age' => 30,
            'gender' => PatientCaseGender::Male,
            'cancer_type_id' => $this->cancerType->id,
            'district_id' => $this->district->id,
            'story_bn' => 'বিবরণ',
            'amount_needed' => 80000,
            'status' => PatientCaseStatus::Published,
            'published_at' => Carbon::now()->subDays(10),
            'expires_at' => Carbon::now()->addDays(20),
            'created_by' => $this->adminUser->id,
        ]);

        // Execute Job
        $job = new NotifyCaseExpiring();
        $notifiedCount = $job->handle();

        $this->assertEquals(1, $notifiedCount);

        // Verify Database Notification was stored for admin user
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->adminUser->id,
            'notifiable_type' => User::class,
        ]);
    }

    public function test_extend_duration_adds_update_and_extends_expiry_by_30_days(): void
    {
        $case = PatientCase::create([
            'real_name' => 'নবায়নযোগ্য রোগী',
            'display_name_bn' => 'নবায়নযোগ্য রোগী',
            'age' => 45,
            'gender' => PatientCaseGender::Female,
            'cancer_type_id' => $this->cancerType->id,
            'district_id' => $this->district->id,
            'story_bn' => 'বিবরণ',
            'amount_needed' => 100000,
            'status' => PatientCaseStatus::Published,
            'published_at' => Carbon::now()->subDays(28),
            'expires_at' => Carbon::now()->addDays(2),
            'created_by' => $this->adminUser->id,
        ]);

        $initialExpiry = $case->expires_at->copy();

        // Simulate "মেয়াদ বাড়াও" Action execution
        $this->actingAs($this->adminUser);

        $case->updates()->create([
            'update_date' => Carbon::now(),
            'note_bn' => 'রোগীর ৩য় ধাপের কেমোথেরাপির নতুন অগ্রগতি নোট।',
            'is_public' => true,
            'created_by' => $this->adminUser->id,
        ]);

        $case->expires_at = $case->expires_at->copy()->addDays(30);
        $case->save();

        $case->refresh();

        $this->assertEquals($initialExpiry->addDays(30)->toDateString(), $case->expires_at->toDateString());
        $this->assertCount(1, $case->updates);
        $this->assertEquals('রোগীর ৩য় ধাপের কেমোথেরাপির নতুন অগ্রগতি নোট।', $case->updates->first()->note_bn);
    }

    public function test_extend_duration_reactivates_expired_case(): void
    {
        $expiredCase = PatientCase::create([
            'real_name' => 'মেয়াদোত্তীর্ণ রোগী',
            'display_name_bn' => 'মেয়াদোত্তীর্ণ রোগী',
            'age' => 55,
            'gender' => PatientCaseGender::Male,
            'cancer_type_id' => $this->cancerType->id,
            'district_id' => $this->district->id,
            'story_bn' => 'বিবরণ',
            'amount_needed' => 100000,
            'status' => PatientCaseStatus::Expired,
            'published_at' => Carbon::now()->subDays(35),
            'expires_at' => Carbon::now()->subDays(5),
            'created_by' => $this->adminUser->id,
        ]);

        // Reactivate & Extend
        $expiredCase->updates()->create([
            'update_date' => Carbon::now(),
            'note_bn' => 'নতুন প্রেসক্রিপশন ও ডাক্তারের পরামর্শ সাপেক্ষে কেসটি পুনরায় চালু করা হলো।',
            'is_public' => true,
            'created_by' => $this->adminUser->id,
        ]);

        $expiredCase->expires_at = Carbon::now()->addDays(30);
        $expiredCase->status = PatientCaseStatus::Published;
        $expiredCase->save();

        $expiredCase->refresh();

        $this->assertEquals(PatientCaseStatus::Published, $expiredCase->status);
        $this->assertTrue($expiredCase->expires_at->isFuture());
    }
}
