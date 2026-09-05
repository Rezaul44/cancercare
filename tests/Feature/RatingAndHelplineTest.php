<?php

namespace Tests\Feature;

use App\Filament\Resources\DoctorRatingSubmissionResource\Pages\ListDoctorRatingSubmissions;
use App\Filament\Resources\HelplineLogResource\Pages\ListHelplineLogs;
use App\Models\CancerType;
use App\Models\Doctor;
use App\Models\DoctorRatingSubmission;
use App\Models\DoctorRatingSummary;
use App\Models\District;
use App\Models\HelplineLog;
use App\Models\PatientCase;
use App\Models\User;
use App\Services\RatingAggregationService;
use App\Services\RatingSubmissionService;
use Database\Seeders\RatingCriteriaSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Phase 7 — মাঠকর্মীর রেটিং ফর্ম, রেটিং যাচাই/aggregation, ও হেল্পলাইন যাচাই করে।
 */
class RatingAndHelplineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        (new RolePermissionSeeder)->run();
        (new RatingCriteriaSeeder)->run();
    }

    public function test_field_agent_can_submit_a_rating_and_duplicate_phone_is_rejected(): void
    {
        $doctor = $this->makeDoctor();
        $fieldAgent = $this->loginAsFieldAgent();

        $payload = [
            'doctor_id' => $doctor->id,
            'phone' => '01712345678',
            'answers' => ['explains_clearly' => 1, 'listens_well' => 1, 'not_rushed' => 0],
        ];

        $this->actingAs($fieldAgent)
            ->postJson(route('field.rating.store'), $payload)
            ->assertCreated();

        $this->assertDatabaseCount('doctor_rating_submissions', 1);
        $submission = DoctorRatingSubmission::first();
        $this->assertSame($doctor->id, $submission->doctor_id);
        $this->assertSame('field_hospital', $submission->source);
        $this->assertFalse($submission->is_verified);

        $this->actingAs($fieldAgent)
            ->postJson(route('field.rating.store'), $payload)
            ->assertStatus(422);

        $this->assertDatabaseCount('doctor_rating_submissions', 1);
    }

    public function test_wrong_role_cannot_reach_the_rating_form(): void
    {
        $user = User::factory()->create();
        $user->assignRole('verification_officer');

        $this->actingAs($user)->get(route('field.rating.create'))->assertForbidden();
    }

    public function test_guest_is_redirected_to_field_login(): void
    {
        $this->get(route('field.rating.create'))->assertRedirect(route('field.login'));
    }

    public function test_verifying_a_submission_in_filament_sets_is_verified_and_verified_by(): void
    {
        $doctor = $this->makeDoctor();
        $fieldAgent = $this->loginAsFieldAgent();
        $verifier = User::factory()->create();
        $verifier->assignRole('verification_officer');

        $submission = app(RatingSubmissionService::class)->submit(
            ['phone' => '01711112222', 'answers' => ['explains_clearly' => true], 'free_comment_bn' => null],
            $doctor,
            $fieldAgent,
            null
        );

        Livewire::actingAs($verifier)
            ->test(ListDoctorRatingSubmissions::class)
            ->callTableAction('verify', $submission)
            ->assertHasNoTableActionErrors();

        $submission->refresh();
        $this->assertTrue($submission->is_verified);
        $this->assertSame($verifier->id, $submission->verified_by);
    }

    public function test_recalculate_summaries_publishes_only_at_the_ten_submission_threshold(): void
    {
        $doctor = $this->makeDoctor();
        $fieldAgent = $this->loginAsFieldAgent();
        $verifier = User::factory()->create();
        $verifier->assignRole('verification_officer');

        $service = app(RatingSubmissionService::class);
        $aggregation = app(RatingAggregationService::class);

        for ($i = 0; $i < 9; $i++) {
            $submission = $service->submit(
                ['phone' => '0171' . str_pad((string) $i, 7, '0', STR_PAD_LEFT), 'answers' => ['explains_clearly' => true], 'free_comment_bn' => null],
                $doctor,
                $fieldAgent,
                null
            );
            $aggregation->verifySubmission($submission, $verifier);
        }

        $aggregation->recalculateForDoctor($doctor);
        $summary = DoctorRatingSummary::find($doctor->id);
        $this->assertSame(9, $summary->total_count);
        $this->assertFalse($summary->is_published);

        // দশম যাচাইকৃত জমা — এখন প্রকাশযোগ্য হওয়া উচিত
        $tenth = $service->submit(
            ['phone' => '01719999999', 'answers' => ['explains_clearly' => true], 'free_comment_bn' => null],
            $doctor,
            $fieldAgent,
            null
        );
        $aggregation->verifySubmission($tenth, $verifier);
        $aggregation->recalculateForDoctor($doctor);

        $summary->refresh();
        $this->assertSame(10, $summary->total_count);
        $this->assertTrue($summary->is_published);
    }

    public function test_helpline_create_case_action_creates_draft_case_and_links_it_back(): void
    {
        $district = $this->makeDistrict();
        $cancerType = $this->makeCancerType();
        $supportAgent = User::factory()->create();
        $supportAgent->assignRole('support_agent');

        $log = HelplineLog::create([
            'channel' => 'phone',
            'caller_phone' => '01711112222',
            'district_id' => $district->id,
            'cancer_type_id' => $cancerType->id,
            'topic' => 'case_application',
            'summary_bn' => 'রোগীর জন্য আর্থিক সহায়তা দরকার',
            'outcome' => 'follow_up_needed',
            'handled_by' => $supportAgent->id,
        ]);

        Livewire::actingAs($supportAgent)
            ->test(ListHelplineLogs::class)
            ->callTableAction('createCase', $log, data: [
                'real_name' => 'রহিমা বেগম',
                'display_name_bn' => 'রহিমা বেগম',
                'age' => 42,
                'gender' => 'female',
                'cancer_type_id' => $cancerType->id,
                'district_id' => $district->id,
                'story_bn' => 'রোগীর কথা...',
                'amount_needed' => 50000,
            ])
            ->assertHasNoTableActionErrors();

        $log->refresh();
        $this->assertNotNull($log->linked_case_id);
        $this->assertSame('case_created', $log->outcome->value);

        $case = PatientCase::find($log->linked_case_id);
        $this->assertNotNull($case);
        $this->assertSame('draft', $case->status->value);
        $this->assertSame($supportAgent->id, $case->created_by);
    }

    private function loginAsFieldAgent(): User
    {
        $user = User::factory()->create();
        $user->assignRole('field_agent');

        return $user;
    }

    private function makeDoctor(array $overrides = []): Doctor
    {
        return Doctor::create(array_merge([
            'name_bn' => 'ডা. সাদিয়া রহমান',
            'name_en' => 'Sadia Rahman',
            'bmdc_number' => 'BMDC-'.uniqid(),
            'photo_path' => 'doctors/photo.jpg',
            'degrees_line_bn' => 'এমবিবিএস, এফসিপিএস',
            'experience_years' => 10,
            'current_position_bn' => 'সহকারী অধ্যাপক',
            'gender' => 'female',
            'status' => 'published',
            'doctor_approved_at' => now(),
            'rotation_seed' => random_int(1, 1000),
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
