<?php

namespace Tests\Feature;

use App\Filament\Resources\DoctorApplicationResource;
use App\Filament\Resources\DoctorApplicationResource\Pages\ListDoctorApplications;
use App\Filament\Resources\DoctorApplicationResource\Pages\ViewDoctorApplication;
use App\Models\Doctor;
use App\Models\DoctorApplication;
use App\Models\DoctorTimeline;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

/**
 * Filament-এর DoctorApplicationResource — তালিকা/ফিল্টার/view + যাচাই/অনুমোদন/বাতিল action,
 * অ্যাক্সেস কন্ট্রোল, এবং activity log যাচাই করে।
 */
class DoctorApplicationResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        (new RolePermissionSeeder)->run();
    }

    public function test_super_admin_can_access_the_list_and_view_pages(): void
    {
        $user = $this->makeUser('super_admin');
        $application = $this->makeApplication();

        $this->actingAs($user)->get(DoctorApplicationResource::getUrl('index'))->assertOk();
        $this->actingAs($user)->get(DoctorApplicationResource::getUrl('view', ['record' => $application]))->assertOk();
    }

    public function test_verification_officer_can_access_the_list_and_view_pages(): void
    {
        $user = $this->makeUser('verification_officer');
        $application = $this->makeApplication();

        $this->actingAs($user)->get(DoctorApplicationResource::getUrl('index'))->assertOk();
        $this->actingAs($user)->get(DoctorApplicationResource::getUrl('view', ['record' => $application]))->assertOk();
    }

    public function test_other_roles_cannot_access_the_resource(): void
    {
        // support_agent-এর 'doctor_applications.view' আছে কিন্তু 'doctor_applications.manage' নেই —
        // এই resource শুধু super_admin/verification_officer-এর জন্য হওয়ায় support_agent-ও ঢুকতে পারবে না।
        $user = $this->makeUser('support_agent');
        $application = $this->makeApplication();

        $this->actingAs($user)->get(DoctorApplicationResource::getUrl('index'))->assertForbidden();
        $this->actingAs($user)->get(DoctorApplicationResource::getUrl('view', ['record' => $application]))->assertForbidden();
    }

    public function test_user_with_no_role_cannot_access_the_resource(): void
    {
        $user = User::factory()->create();
        $application = $this->makeApplication();

        $this->actingAs($user)->get(DoctorApplicationResource::getUrl('index'))->assertForbidden();
        $this->actingAs($user)->get(DoctorApplicationResource::getUrl('view', ['record' => $application]))->assertForbidden();
    }

    public function test_list_shows_name_bmdc_number_and_status_badge(): void
    {
        $user = $this->makeUser('super_admin');
        $application = $this->makeApplication(['full_name' => 'ডা. সাদিয়া রহমান', 'bmdc_number' => 'A-99001']);

        Livewire::actingAs($user)
            ->test(ListDoctorApplications::class)
            ->assertCanSeeTableRecords([$application])
            ->assertSee('ডা. সাদিয়া রহমান')
            ->assertSee('A-99001')
            ->assertSee('জমা দেওয়া হয়েছে');
    }

    public function test_list_can_be_filtered_by_status(): void
    {
        $user = $this->makeUser('super_admin');
        $submitted = $this->makeApplication(['bmdc_number' => 'F-1', 'status' => 'submitted']);
        $rejected = $this->makeApplication(['bmdc_number' => 'F-2', 'status' => 'rejected']);

        Livewire::actingAs($user)
            ->test(ListDoctorApplications::class)
            ->filterTable('status', 'rejected')
            ->assertCanSeeTableRecords([$rejected])
            ->assertCanNotSeeTableRecords([$submitted]);
    }

    public function test_view_page_shows_application_details_and_signed_certificate_links(): void
    {
        Storage::fake('s3_private');
        Storage::disk('s3_private')->put('doctor-applications/x/photo.jpg', 'fake');
        Storage::disk('s3_private')->put('doctor-applications/x/bmdc/cert.pdf', 'fake');
        Storage::disk('s3_private')->put('doctor-applications/x/degrees/d1.pdf', 'fake');

        $user = $this->makeUser('super_admin');
        $application = $this->makeApplication([
            'photo_path' => 'doctor-applications/x/photo.jpg',
            'bmdc_certificate_path' => 'doctor-applications/x/bmdc/cert.pdf',
        ]);
        $application->update([
            'degrees' => array_merge($application->degrees, ['certificate_paths' => ['doctor-applications/x/degrees/d1.pdf']]),
        ]);

        Livewire::actingAs($user)
            ->test(ViewDoctorApplication::class, ['record' => $application->getRouteKey()])
            ->assertSee($application->full_name)
            ->assertSee('ছবি দেখুন', false)
            ->assertSee('সনদ দেখুন', false)
            ->assertSee('সনদ #1', false)
            // signed URL অবশ্যই relative (host ছাড়া) হতে হবে — ServeFile::hasValidSignature()
            // hasValidRelativeSignature() দিয়ে validate করে, absolute URL সই করলে signature কখনো মিলবে না।
            ->assertSee(route('storage.s3_private', ['path' => $application->photo_path], absolute: false), false);
    }

    public function test_verify_action_saves_checklist_sets_status_and_logs_activity(): void
    {
        $user = $this->makeUser('verification_officer');
        $application = $this->makeApplication();

        Livewire::actingAs($user)
            ->test(ListDoctorApplications::class)
            ->callTableAction('verify', $application, data: [
                'bmdc_verified' => true,
                'degree_verified' => true,
                'note' => 'সব ঠিক আছে',
            ])
            ->assertHasNoTableActionErrors();

        $application->refresh();

        $this->assertSame('under_review', $application->status);
        $this->assertTrue($application->verification_checklist['bmdc_verified']);
        $this->assertTrue($application->verification_checklist['degree_verified']);
        $this->assertSame('সব ঠিক আছে', $application->verification_checklist['note']);
        $this->assertTrue($application->isVerified());

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => DoctorApplication::class,
            'subject_id' => $application->id,
            'causer_id' => $user->id,
            'description' => 'doctor_application.verified',
        ]);
    }

    public function test_approve_action_is_hidden_until_verified(): void
    {
        $user = $this->makeUser('verification_officer');
        $application = $this->makeApplication();

        Livewire::actingAs($user)
            ->test(ListDoctorApplications::class)
            ->assertTableActionHidden('approve', $application);
    }

    public function test_approve_action_creates_doctor_with_timeline_and_chambers_and_logs_activity(): void
    {
        $user = $this->makeUser('verification_officer');
        $district = $this->makeDistrict();
        $application = $this->makeApplication();

        $service = app(\App\Services\DoctorApplicationService::class);
        $service->markVerified($application, ['bmdc_verified' => true, 'degree_verified' => true, 'note' => null], $user);
        $application->refresh();

        // Repeater-এর জন্য প্রথমে fillForm() ডিফল্ট বসে (application->chambers থেকে, district_id/time_from/time_to
        // ছাড়া) — সেই একই আইটেমের ওপর leaf-path দিয়ে বাকি ফিল্ড set করলে Livewire-এর নতুন key-generation-এর
        // সাথে collide করে, তাই পুরো 'chambers' array-টা একবারে replace করা হচ্ছে।
        $testable = Livewire::actingAs($user)->test(ListDoctorApplications::class);
        $testable->mountTableAction('approve', $application);
        $testable->set('mountedTableActionsData.0.gender', 'female');
        $testable->set('mountedTableActionsData.0.experience_years', 12);
        $testable->set('mountedTableActionsData.0.current_position', 'সিনিয়র কনসালট্যান্ট');
        $testable->set('mountedTableActionsData.0.chambers', [
            [
                'name_bn' => 'স্কয়ার হাসপাতাল',
                'address_bn' => 'পান্থপথ, ঢাকা',
                'district_id' => $district->id,
                'type' => 'private',
                'fee' => 1000,
                'days_bn' => 'রবি, মঙ্গল',
                'time_from' => '09:00',
                'time_to' => '13:00',
            ],
        ]);
        $testable->callMountedTableAction();
        $testable->assertHasNoTableActionErrors();

        $application->refresh();

        $this->assertSame('approved', $application->status);
        $this->assertNotNull($application->doctor_id);

        $doctor = Doctor::find($application->doctor_id);
        $this->assertNotNull($doctor);
        $this->assertSame($application->full_name, $doctor->name_bn);
        $this->assertSame($application->bmdc_number, $doctor->bmdc_number);
        $this->assertSame('female', $doctor->gender);
        $this->assertSame(12, $doctor->experience_years);
        $this->assertSame('সিনিয়র কনসালট্যান্ট', $doctor->current_position_bn);
        $this->assertNotNull($doctor->bmdc_verified_at);

        $this->assertSame(1, DoctorTimeline::where('doctor_id', $doctor->id)->count());
        $this->assertSame(1, $doctor->chambers()->count());
        $this->assertSame($district->id, $doctor->chambers()->first()->district_id);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => DoctorApplication::class,
            'subject_id' => $application->id,
            'causer_id' => $user->id,
            'description' => 'doctor_application.approved',
        ]);
    }

    public function test_reject_action_requires_reason_and_logs_activity(): void
    {
        $user = $this->makeUser('super_admin');
        $application = $this->makeApplication();

        Livewire::actingAs($user)
            ->test(ListDoctorApplications::class)
            ->callTableAction('reject', $application, data: ['reason' => ''])
            ->assertHasTableActionErrors(['reason' => 'required']);

        Livewire::actingAs($user)
            ->test(ListDoctorApplications::class)
            ->callTableAction('reject', $application, data: ['reason' => 'BMDC নম্বর সঠিক নয়'])
            ->assertHasNoTableActionErrors();

        $application->refresh();

        $this->assertSame('rejected', $application->status);
        $this->assertSame('BMDC নম্বর সঠিক নয়', $application->review_note);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => DoctorApplication::class,
            'subject_id' => $application->id,
            'causer_id' => $user->id,
            'description' => 'doctor_application.rejected',
        ]);
    }

    private function makeUser(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function makeApplication(array $overrides = []): DoctorApplication
    {
        return DoctorApplication::create(array_merge([
            'full_name' => 'ডা. পরীক্ষা',
            'bmdc_number' => 'BMDC-'.uniqid(),
            'phone' => '01712345678',
            'email' => 'doctor@example.com',
            'photo_path' => 'doctor-applications/x/photo.jpg',
            'bmdc_certificate_path' => 'doctor-applications/x/bmdc/cert.pdf',
            'experience_years' => 10,
            'current_position' => 'কনসালট্যান্ট',
            'degrees' => [
                'primary' => 'MBBS',
                'specialized' => 'FCPS (Oncology)',
                'fellowship' => null,
                'certificate_paths' => ['doctor-applications/x/degrees/d1.pdf'],
            ],
            'timeline' => [
                ['year_label' => '২০০১', 'title_bn' => 'MBBS', 'institution_bn' => 'ঢাকা মেডিকেল কলেজ'],
            ],
            'doctor_type_ids' => [],
            'cancer_type_ids' => [],
            'chambers' => [
                ['name_bn' => 'স্কয়ার হাসপাতাল', 'address_bn' => 'পান্থপথ, ঢাকা', 'fee' => 1000, 'type' => 'private', 'days_bn' => 'রবি, মঙ্গল'],
            ],
            'extra_services' => ['whatsapp' => false, 'second_opinion' => true, 'telemedicine' => false],
            'preferred_call_time' => 'বিকেল',
            'preferred_call_day' => 'যেকোনো দিন',
            'declarations' => ['information_accurate' => true, 'agreed_at' => now()->toDateTimeString()],
            'status' => 'submitted',
        ], $overrides));
    }

    private function makeDistrict(): \App\Models\District
    {
        $divisionId = DB::table('divisions')->insertGetId([
            'name_bn' => 'ঢাকা',
            'name_en' => 'Dhaka',
            'slug' => 'dhaka-'.uniqid(),
        ]);

        return \App\Models\District::create([
            'division_id' => $divisionId,
            'name_bn' => 'ঢাকা',
            'name_en' => 'Dhaka',
            'slug' => 'dhaka-'.uniqid(),
            'distance_tier' => 'local',
            'has_cancer_center' => true,
        ]);
    }
}
