<?php

namespace Tests\Feature;

use App\Enums\PaymentStatus;
use App\Enums\SecondOpinionStatus;
use App\Models\CancerType;
use App\Models\District;
use App\Models\Doctor;
use App\Models\Payment;
use App\Models\SecondOpinionRequest;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Phase 6.2–6.4 — /second-opinion ফর্ম, পেমেন্ট গেটওয়ে, ও ডাক্তার পোর্টাল যাচাই করে।
 */
class SecondOpinionAndDoctorPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        (new RolePermissionSeeder)->run();
    }

    public function test_valid_submission_creates_request_and_files_with_fee_frozen_from_doctor(): void
    {
        Storage::fake('s3_private');
        Http::fake([
            '*/gwprocess/v4/api.php' => Http::response(['status' => 'SUCCESS', 'GatewayPageURL' => 'https://sandbox.sslcommerz.com/pay/xyz']),
        ]);

        $cancerType = $this->makeCancerType();
        $district = $this->makeDistrict();
        $doctor = $this->makeDoctor(['second_opinion_fee' => 1500]);

        $response = $this->post(route('second-opinion.request.store'), $this->validPayload($cancerType->id, $district->id, $doctor->id));

        $response->assertRedirect('https://sandbox.sslcommerz.com/pay/xyz');

        $this->assertDatabaseCount('second_opinion_requests', 1);

        $request = SecondOpinionRequest::first();
        $this->assertSame(1500, $request->fee);
        $this->assertSame(SecondOpinionStatus::PendingPayment, $request->status);
        $this->assertSame($doctor->id, $request->doctor_id);
        $this->assertMatchesRegularExpression('/^SO-\d{4}-\d{4}$/', $request->request_code);

        $this->assertDatabaseCount('second_opinion_files', 1);
        Storage::disk('s3_private')->assertExists($request->files->first()->file_path);

        $payment = Payment::first();
        $this->assertSame(1500, $payment->amount);
        $this->assertSame('sslcommerz', $payment->gateway->value);
        $this->assertSame(PaymentStatus::Initiated, $payment->status);
    }

    public function test_doctor_selection_is_restricted_to_those_offering_second_opinion(): void
    {
        Storage::fake('s3_private');

        $cancerType = $this->makeCancerType();
        $district = $this->makeDistrict();
        $notOffering = $this->makeDoctor(['offers_second_opinion' => false]);

        $response = $this->post(
            route('second-opinion.request.store'),
            $this->validPayload($cancerType->id, $district->id, $notOffering->id)
        );

        $response->assertSessionHasErrors(['doctor_id']);
        $this->assertDatabaseCount('second_opinion_requests', 0);
    }

    public function test_successful_payment_callback_marks_request_submitted(): void
    {
        $cancerType = $this->makeCancerType();
        $district = $this->makeDistrict();
        $doctor = $this->makeDoctor(['second_opinion_fee' => 1000]);

        $request = SecondOpinionRequest::create([
            'patient_name' => 'রোগী',
            'age' => 40,
            'cancer_type_id' => $cancerType->id,
            'current_status' => 'ongoing',
            'question_bn' => 'প্রশ্ন',
            'phone' => '01700000000',
            'district_id' => $district->id,
            'doctor_id' => $doctor->id,
            'fee' => 1000,
            'status' => SecondOpinionStatus::PendingPayment,
            'expected_hours' => 72,
        ]);

        $payment = $request->paymentAttempts()->create([
            'gateway' => 'sslcommerz',
            'amount' => 1000,
            'status' => PaymentStatus::Initiated,
        ]);

        Http::fake([
            '*/validator/api/validationserverAPI.php*' => Http::response(['status' => 'VALID', 'tran_id' => 'TXN123']),
        ]);

        $response = $this->get(route('payments.success', $payment).'?val_id=abc123');

        $response->assertOk();

        $payment->refresh();
        $request->refresh();

        $this->assertSame(PaymentStatus::Success, $payment->status);
        $this->assertSame('TXN123', $payment->transaction_id);
        $this->assertSame(SecondOpinionStatus::Submitted, $request->status);
        $this->assertSame($payment->id, $request->payment_id);
    }

    public function test_doctor_cannot_view_another_doctors_second_opinion_request(): void
    {
        $cancerType = $this->makeCancerType();
        $district = $this->makeDistrict();

        $ownDoctor = $this->makeDoctor();
        $ownUser = User::factory()->create();
        $ownUser->assignRole('doctor');
        $ownDoctor->update(['user_id' => $ownUser->id]);

        $otherDoctor = $this->makeDoctor(['bmdc_number' => 'BMDC-'.uniqid()]);

        $request = SecondOpinionRequest::create([
            'patient_name' => 'রোগী',
            'age' => 40,
            'cancer_type_id' => $cancerType->id,
            'current_status' => 'ongoing',
            'question_bn' => 'প্রশ্ন',
            'phone' => '01700000000',
            'district_id' => $district->id,
            'doctor_id' => $otherDoctor->id,
            'fee' => 1000,
            'status' => SecondOpinionStatus::Submitted,
            'expected_hours' => 72,
        ]);

        $this->actingAs($ownUser)
            ->get(route('doctor.second-opinions.show', $request))
            ->assertForbidden();
    }

    public function test_doctor_login_flow_and_dashboard_access(): void
    {
        $doctor = $this->makeDoctor();
        $user = User::factory()->create(['password' => bcrypt('password123')]);
        $user->assignRole('doctor');
        $doctor->update(['user_id' => $user->id]);

        // লগইন ছাড়া ড্যাশবোর্ডে গেলে ডাক্তার লগইন পাতায় পাঠাবে (role:doctor middleware alias ঠিকমতো কাজ করছে কিনা তার regression check)
        $this->get(route('doctor.dashboard'))->assertRedirect(route('doctor.login'));

        $this->post(route('doctor.login.store'), [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertRedirect(route('doctor.dashboard'));

        $this->actingAs($user)->get(route('doctor.dashboard'))->assertOk();
    }

    public function test_bare_doctor_role_cannot_access_filament_admin_panel(): void
    {
        $user = User::factory()->create();
        $user->assignRole('doctor');

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(int $cancerTypeId, int $districtId, int $doctorId): array
    {
        return [
            'reports' => [UploadedFile::fake()->create('report.pdf', 200, 'application/pdf')],
            'patient_name' => 'রহিমা বেগম',
            'age' => 45,
            'cancer_type_id' => $cancerTypeId,
            'current_status' => 'ongoing',
            'treatments_done_bn' => 'কেমোথেরাপি চলছে',
            'question_bn' => 'পরবর্তী ধাপ কী হওয়া উচিত?',
            'phone' => '01712345678',
            'district_id' => $districtId,
            'doctor_id' => $doctorId,
            'gateway' => 'sslcommerz',
        ];
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
            'offers_second_opinion' => true,
            'offers_whatsapp' => false,
            'second_opinion_fee' => 1000,
            'status' => 'published',
            'doctor_approved_at' => now(),
            'rotation_seed' => random_int(1, 1000),
        ], $overrides));
    }

    private function makeDistrict(): District
    {
        $divisionId = DB::table('divisions')->insertGetId([
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
