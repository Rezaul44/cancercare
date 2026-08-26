<?php

namespace Tests\Feature;

use App\Models\DoctorApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * docs/prototypes/onboarding.html-এর "ডাক্তার যুক্ত হওয়া" ৪-ধাপ আবেদন ফর্ম (GET/POST /for-doctors) যাচাই করে।
 */
class DoctorApplicationFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_shows_the_four_step_application_form(): void
    {
        $response = $this->get(route('doctors.apply'));

        $response->assertOk();
        $response->assertSee('পরিচয় ও যোগাযোগ');
        $response->assertSee('যোগ্যতা ও পেশাগত জীবন');
        $response->assertSee('বিশেষত্ব ও চেম্বার');
        $response->assertSee('ঘোষণা ও সম্মতি');
        $response->assertSee('ফর্ম জমা দেওয়ার পর যা হবে');
    }

    public function test_valid_submission_creates_application_and_stores_files_on_private_disk(): void
    {
        Storage::fake('s3_private');

        [$doctorTypeId, $cancerTypeId] = $this->seedTypes();

        $response = $this->post(route('doctors.apply.store'), $this->validPayload($doctorTypeId, $cancerTypeId));

        $response->assertRedirect(route('doctors.apply'));
        $response->assertSessionHas('application_submitted', true);

        $this->assertDatabaseCount('doctor_applications', 1);

        $application = DoctorApplication::first();
        $this->assertSame('submitted', $application->status);
        $this->assertSame('সাদিয়া রহমান', $application->full_name);
        $this->assertSame('A-34821', $application->bmdc_number);
        $this->assertNotNull($application->photo_path);
        $this->assertNotNull($application->bmdc_certificate_path);

        Storage::disk('s3_private')->assertExists($application->photo_path);
        Storage::disk('s3_private')->assertExists($application->bmdc_certificate_path);
        foreach ($application->degrees['certificate_paths'] as $path) {
            Storage::disk('s3_private')->assertExists($path);
        }

        $this->assertSame('MBBS — ঢাকা মেডিকেল কলেজ, ২০০১', $application->degrees['primary']);
        $this->assertSame([$doctorTypeId], $application->doctor_type_ids);
        $this->assertSame([$cancerTypeId], $application->cancer_type_ids);
        $this->assertTrue($application->extra_services['second_opinion']);
        $this->assertFalse($application->extra_services['whatsapp']);
        $this->assertTrue($application->declarations['information_accurate']);
        $this->assertArrayHasKey('agreed_at', $application->declarations);
        $this->assertCount(1, $application->chambers);
        $this->assertCount(1, $application->timeline);
    }

    public function test_missing_required_fields_redirects_back_with_errors_and_no_row_created(): void
    {
        Storage::fake('s3_private');

        $response = $this->post(route('doctors.apply.store'), []);

        $response->assertRedirect();
        $response->assertSessionHasErrors([
            'full_name', 'bmdc_number', 'phone', 'email', 'photo', 'bmdc_certificate',
            'primary_degree', 'specialized_degree', 'experience_years', 'current_position',
            'timeline', 'degree_certificates', 'doctor_types', 'cancer_types', 'chambers',
            'declarations', 'preferred_call_time', 'preferred_call_day',
        ]);

        $this->assertDatabaseCount('doctor_applications', 0);
    }

    public function test_declaration_must_be_accepted_not_just_present(): void
    {
        [$doctorTypeId, $cancerTypeId] = $this->seedTypes();
        Storage::fake('s3_private');

        $payload = $this->validPayload($doctorTypeId, $cancerTypeId);
        $payload['declarations']['bmdc_valid'] = '0';

        $response = $this->post(route('doctors.apply.store'), $payload);

        $response->assertSessionHasErrors(['declarations.bmdc_valid']);
        $this->assertDatabaseCount('doctor_applications', 0);
    }

    public function test_error_redirect_reopens_form_at_the_step_containing_the_error(): void
    {
        [$doctorTypeId, $cancerTypeId] = $this->seedTypes();
        Storage::fake('s3_private');

        $payload = $this->validPayload($doctorTypeId, $cancerTypeId);
        unset($payload['doctor_types']); // ধাপ ৩-এর ফিল্ড বাদ

        $this->post(route('doctors.apply.store'), $payload);

        $response = $this->get(route('doctors.apply'));

        $response->assertOk();
        $response->assertSee('step: 3', false);
    }

    public function test_thank_you_page_explains_verification_timeline_after_submission(): void
    {
        $response = $this->withSession(['application_submitted' => true])->get(route('doctors.apply'));

        $response->assertOk();
        $response->assertSee('আবেদন জমা হয়েছে');
        $response->assertSee('৫–৭ কর্মদিবসের মধ্যে');
        $response->assertSee('ফোন করব');
    }

    /**
     * @return array{0: int, 1: int} [doctorTypeId, cancerTypeId]
     */
    private function seedTypes(): array
    {
        $doctorTypeId = DB::table('doctor_types')->insertGetId([
            'key' => 'surgical_oncologist',
            'label_bn' => 'সার্জিক্যাল অনকোলজিস্ট',
            'label_en' => 'Surgical Oncologist',
        ]);

        $cancerTypeId = DB::table('cancer_types')->insertGetId([
            'name_bn' => 'স্তন',
            'name_en' => 'Breast Cancer',
            'slug' => 'breast-cancer',
            'icon' => 'ribbon',
            'color_key' => 'pink',
            'short_description_bn' => 'বিবরণ',
            'is_common' => true,
            'doctor_count_cache' => 0,
            'hospital_count_cache' => 0,
            'guide_published' => false,
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$doctorTypeId, $cancerTypeId];
    }

    private function validPayload(int $doctorTypeId, int $cancerTypeId): array
    {
        return [
            'full_name' => 'সাদিয়া রহমান',
            'bmdc_number' => 'A-34821',
            'phone' => '01712345678',
            'email' => 'doctor@example.com',
            'photo' => UploadedFile::fake()->image('photo.jpg'),
            'bmdc_certificate' => UploadedFile::fake()->create('bmdc.pdf', 200, 'application/pdf'),
            'primary_degree' => 'MBBS — ঢাকা মেডিকেল কলেজ, ২০০১',
            'specialized_degree' => 'FCPS (Oncology) — BCPS, ২০০৭',
            'fellowship' => null,
            'experience_years' => 16,
            'current_position' => 'সিনিয়র কনসালট্যান্ট, স্কয়ার হাসপাতাল',
            'timeline' => [
                ['year_label' => '২০০১', 'title_bn' => 'MBBS', 'institution_bn' => 'ঢাকা মেডিকেল কলেজ'],
            ],
            'degree_certificates' => [
                UploadedFile::fake()->create('degree1.pdf', 200, 'application/pdf'),
            ],
            'doctor_types' => [$doctorTypeId],
            'cancer_types' => [$cancerTypeId],
            'chambers' => [
                [
                    'name_bn' => 'স্কয়ার হাসপাতাল',
                    'address_bn' => 'পান্থপথ, ঢাকা',
                    'fee' => 1000,
                    'type' => 'private',
                    'days_bn' => 'রবি, মঙ্গল, বৃহস্পতি',
                ],
            ],
            'extra_services' => ['second_opinion'],
            'declarations' => [
                'information_accurate' => '1',
                'bmdc_valid' => '1',
                'no_payment_for_ranking' => '1',
                'will_notify_changes' => '1',
                'consent_patient_feedback' => '1',
            ],
            'preferred_call_time' => 'বিকেল ৪টা – রাত ৮টা',
            'preferred_call_day' => 'যেকোনো কর্মদিবস',
        ];
    }
}
