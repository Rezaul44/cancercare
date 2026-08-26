<?php

namespace Tests\Feature;

use App\Enums\DoctorStatus;
use App\Models\CancerType;
use App\Models\District;
use App\Models\Division;
use App\Models\Doctor;
use App\Models\DoctorRatingSummary;
use App\Services\DoctorRankingService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Phase 2 সংহত ফিচার টেস্ট — docs/CCB_prompt_playbook.md ধারা ২.৮
 *
 * ১. ranking query কখনো doctor_videos বা payments টেবিল join করে না
 * ২. অপ্রকাশিত (draft) ডাক্তার directory-তে আসে না
 * ৩. rating_summary.is_published=false হলে প্রোফাইলে রেটিং render হয় না
 * ৪. doctor_approved_at null হলে publish করা যায় না
 * ৫. ফিল্টার query string URL-এ প্রতিফলিত হয়
 * ৬. BMDC সনদের ফাইল signed URL ছাড়া অ্যাক্সেস করা যায় না
 */
class Phase2FeatureSuiteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ১. ranking query কখনো doctor_videos বা payments টেবিল join করে না (CLAUDE.md নীতি ১)
     */
    public function test_1_ranking_query_never_joins_forbidden_tables_or_columns(): void
    {
        $service = new DoctorRankingService();
        $query = $service->query();
        $sql = strtolower($query->toSql());

        $forbidden = [
            'doctor_videos',
            'payments',
            'is_paid_production',
            'hospitals',
            'success_rate',
            'priority',
            'featured',
            'sponsored',
        ];

        foreach ($forbidden as $word) {
            $this->assertStringNotContainsString(
                $word,
                $sql,
                "DoctorRankingService query must NEVER reference '{$word}'."
            );
        }
    }

    /**
     * ২. অপ্রকাশিত (draft / pending_approval / suspended) ডাক্তার directory-তে আসে না
     */
    public function test_2_unpublished_draft_doctor_does_not_appear_in_directory(): void
    {
        $publishedDoctor = $this->createDoctor([
            'name_bn' => 'ডা. প্রকাশিত অনকোলজিস্ট',
            'status' => DoctorStatus::Published,
            'doctor_approved_at' => now(),
        ]);

        $draftDoctor = $this->createDoctor([
            'name_bn' => 'ডা. ড্রাফট অনকোলজিস্ট',
            'status' => DoctorStatus::Draft,
            'doctor_approved_at' => null,
        ]);

        $pendingDoctor = $this->createDoctor([
            'name_bn' => 'ডা. অপেক্ষমাণ অনকোলজিস্ট',
            'status' => DoctorStatus::PendingApproval,
            'doctor_approved_at' => null,
        ]);

        $response = $this->get(route('doctors.index'));

        $response->assertOk();
        $response->assertSee('ডা. প্রকাশিত অনকোলজিস্ট');
        $response->assertDontSee('ডা. ড্রাফট অনকোলজিস্ট');
        $response->assertDontSee('ডা. অপেক্ষমাণ অনকোলজিস্ট');
    }

    /**
     * ৩. rating_summary.is_published=false হলে প্রোফাইলে রেটিং render হয় না (CLAUDE.md নীতি ৫)
     */
    public function test_3_rating_summary_not_rendered_on_profile_when_is_published_is_false(): void
    {
        $doctor = $this->createDoctor([
            'status' => DoctorStatus::Published,
            'doctor_approved_at' => now(),
        ]);

        DoctorRatingSummary::create([
            'doctor_id' => $doctor->id,
            'total_count' => 4,
            'criteria_scores' => ['explains_clearly' => 90],
            'overall_score' => 4.5,
            'is_published' => false,
            'last_calculated_at' => now(),
        ]);

        $response = $this->get(route('doctors.show', $doctor));

        $response->assertOk();
        // রেটিং সেকশনের ভেতরের কোনো অংশ রেন্ডার হবে না
        $response->assertDontSee('শুধু যাচাইকৃত রোগীরাই মতামত দিতে পারেন');
        $response->assertSee('নতুন — যথেষ্ট রেটিং নেই');
    }

    /**
     * ৪. doctor_approved_at null হলে publish করা যায় না
     */
    public function test_4_doctor_cannot_be_published_if_doctor_approved_at_is_null(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('doctor_approved_at');

        // doctor_approved_at null রেখে status=published করার চেষ্টা করলে DomainException ছুড়বে
        $this->createDoctor([
            'status' => DoctorStatus::Published,
            'doctor_approved_at' => null,
        ]);
    }

    /**
     * ৪ (অতিরিক্ত). doctor_approved_at থাকলে সফলভাবে publish করা যায়
     */
    public function test_4_doctor_with_approved_at_publishes_successfully(): void
    {
        $doctor = $this->createDoctor([
            'status' => DoctorStatus::Published,
            'doctor_approved_at' => now(),
        ]);

        $this->assertSame(DoctorStatus::Published, $doctor->status);
        $this->assertNotNull($doctor->doctor_approved_at);
    }

    /**
     * ৫. ফিল্টার query string URL ও ফর্মে প্রতিফলিত হয়
     */
    public function test_5_filters_query_string_reflected_in_directory(): void
    {
        $divisionId = \Illuminate\Support\Facades\DB::table('divisions')->insertGetId([
            'name_bn' => 'ঢাকা',
            'name_en' => 'Dhaka',
            'slug' => 'dhaka-div-'.uniqid(),
        ]);
        $district = District::create([
            'division_id' => $divisionId,
            'name_bn' => 'ঢাকা জেলা',
            'name_en' => 'Dhaka District',
            'slug' => 'dhaka-'.uniqid(),
            'distance_tier' => 'local',
            'has_cancer_center' => true,
        ]);

        $cancer = CancerType::create([
            'name_bn' => 'স্তন ক্যান্সার',
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

        $response = $this->get(route('doctors.index', [
            'cancer' => $cancer->slug,
            'district' => $district->slug,
            'sort' => 'fee',
        ]));

        $response->assertOk();
        $response->assertSee('value="'.$cancer->slug.'" selected', false);
        $response->assertSee('value="'.$district->slug.'" selected', false);
        $response->assertSee('value="fee"', false);
    }

    /**
     * ৬. BMDC সনদের ফাইল signed URL ছাড়া অ্যাক্সেস করা যায় না
     */
    public function test_6_bmdc_certificate_file_cannot_be_accessed_without_valid_signed_url(): void
    {
        Storage::fake('s3_private');

        $filePath = 'doctor-applications/test-uuid/bmdc/certificate.pdf';
        Storage::disk('s3_private')->put($filePath, 'fake-pdf-content');

        $unsignedUrl = route('storage.s3_private', ['path' => $filePath], false);

        // সই ছাড়া এক্সেস করলে 403 Forbidden আসবে
        $unsignedResponse = $this->get($unsignedUrl);
        $unsignedResponse->assertForbidden();

        // সঠিক signed URL দিয়ে এক্সেস করলে 200 OK আসবে
        $signedUrl = URL::temporarySignedRoute(
            'storage.s3_private',
            now()->addMinutes(15),
            ['path' => $filePath],
            absolute: false
        );

        $signedResponse = $this->get($signedUrl);
        $signedResponse->assertOk();
    }

    private function createDoctor(array $overrides = []): Doctor
    {
        return Doctor::create(array_merge([
            'name_bn' => 'ডা. পরীক্ষামূলক অনকোলজিস্ট',
            'name_en' => 'Dr. Test Oncologist',
            'slug' => 'dr-test-oncologist-'.uniqid(),
            'bmdc_number' => 'BMDC-'.uniqid(),
            'bmdc_verified_at' => now()->subMonths(3),
            'photo_path' => 'doctors/photo.jpg',
            'degrees_line_bn' => 'এমবিবিএস, এফসিপিএস',
            'experience_years' => 10,
            'current_position_bn' => 'কনসালট্যান্ট',
            'gender' => 'female',
            'patients_treated' => 450,
            'status' => DoctorStatus::Draft,
        ], $overrides));
    }
}
