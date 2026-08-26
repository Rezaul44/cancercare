<?php

namespace Tests\Feature;

use App\Enums\ChamberType;
use App\Enums\DoctorStatus;
use App\Models\CancerType;
use App\Models\Chamber;
use App\Models\Doctor;
use App\Models\DoctorRatingSubmission;
use App\Models\DoctorRatingSummary;
use App\Models\District;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * docs/CCB_database_schema.md ধারা ৩ ও ৪ অনুযায়ী তৈরি migration/model/observer যাচাই করে।
 */
class DoctorDomainTest extends TestCase
{
    use RefreshDatabase;

    private const TABLES = [
        'doctor_applications',
        'doctors',
        'doctor_documents',
        'doctor_timeline',
        'doctor_doctor_type',
        'doctor_cancer_type',
        'doctor_services',
        'doctor_philosophy_points',
        'doctor_videos',
        'chambers',
        'doctor_rating_submissions',
        'doctor_rating_summaries',
    ];

    public function test_all_expected_tables_exist(): void
    {
        foreach (self::TABLES as $table) {
            $this->assertTrue(Schema::hasTable($table), "Table [{$table}] missing");
        }
    }

    public function test_observer_sets_slug_and_rotation_seed_on_create(): void
    {
        $doctor = $this->makeDoctor(['name_en' => 'Sadia Rahman']);

        $this->assertSame('sadia-rahman', $doctor->slug);
        $this->assertGreaterThanOrEqual(0, $doctor->rotation_seed);
        $this->assertLessThanOrEqual(999, $doctor->rotation_seed);
    }

    public function test_observer_appends_suffix_on_slug_collision(): void
    {
        $this->makeDoctor(['name_en' => 'Sadia Rahman', 'bmdc_number' => 'A-1']);
        $second = $this->makeDoctor(['name_en' => 'Sadia Rahman', 'bmdc_number' => 'A-2']);

        $this->assertSame('sadia-rahman-2', $second->slug);
    }

    public function test_published_scope_filters_by_status(): void
    {
        $published = $this->makeDoctor(['bmdc_number' => 'B-1', 'status' => DoctorStatus::Published, 'doctor_approved_at' => now()]);
        $this->makeDoctor(['bmdc_number' => 'B-2', 'status' => DoctorStatus::Draft]);

        $result = Doctor::published()->get();

        $this->assertCount(1, $result);
        $this->assertTrue($result->first()->is($published));
    }

    public function test_verified_scope_filters_by_bmdc_verified_at(): void
    {
        $verified = $this->makeDoctor(['bmdc_number' => 'C-1', 'bmdc_verified_at' => now()]);
        $this->makeDoctor(['bmdc_number' => 'C-2', 'bmdc_verified_at' => null]);

        $result = Doctor::verified()->get();

        $this->assertCount(1, $result);
        $this->assertTrue($result->first()->is($verified));
    }

    public function test_doctor_status_and_chamber_type_cast_to_enums(): void
    {
        $doctor = $this->makeDoctor(['bmdc_number' => 'D-1', 'status' => DoctorStatus::Published, 'doctor_approved_at' => now()]);
        $this->assertInstanceOf(DoctorStatus::class, $doctor->status);

        $district = $this->makeDistrict();
        $chamber = Chamber::create([
            'doctor_id' => $doctor->id,
            'name_bn' => 'টেস্ট চেম্বার',
            'address_bn' => 'ঠিকানা',
            'district_id' => $district->id,
            'type' => ChamberType::Government,
            'fee' => 500,
            'days_bn' => 'রবি, মঙ্গল',
            'time_from' => '09:00',
            'time_to' => '13:00',
            'is_active' => true,
        ]);

        $this->assertInstanceOf(ChamberType::class, $chamber->type);
        $this->assertSame(ChamberType::Government, $chamber->type);
    }

    public function test_doctor_cancer_type_pivot_stores_is_primary(): void
    {
        $doctor = $this->makeDoctor(['bmdc_number' => 'E-1']);
        $cancerType = $this->makeCancerType();

        $doctor->cancerTypes()->attach($cancerType->id, ['is_primary' => true]);

        $attached = $doctor->cancerTypes()->first();
        $this->assertTrue((bool) $attached->pivot->is_primary);
    }

    public function test_doctor_rating_summary_primary_key_is_doctor_id(): void
    {
        $doctor = $this->makeDoctor(['bmdc_number' => 'F-1']);

        $summary = DoctorRatingSummary::create([
            'doctor_id' => $doctor->id,
            'total_count' => 12,
            'criteria_scores' => ['explains_clearly' => 96],
            'overall_score' => 4.8,
            'is_published' => true,
            'last_calculated_at' => now(),
        ]);

        $this->assertSame($doctor->id, $summary->getKey());
        $this->assertTrue($doctor->ratingSummary()->exists());

        $this->expectException(QueryException::class);
        DoctorRatingSummary::create([
            'doctor_id' => $doctor->id,
            'total_count' => 1,
            'criteria_scores' => [],
            'last_calculated_at' => now(),
        ]);
    }

    public function test_doctor_rating_submission_unique_per_doctor_and_patient_hash(): void
    {
        $doctor = $this->makeDoctor(['bmdc_number' => 'G-1']);
        $collector = DB::table('users')->insertGetId([
            'name' => 'Field Agent',
            'email' => 'agent@example.com',
            'password' => bcrypt('secret'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $payload = [
            'doctor_id' => $doctor->id,
            'collected_by' => $collector,
            'source' => 'field_hospital',
            'patient_phone_hash' => 'hash-1',
            'proof_type' => 'none',
            'answers' => ['explains_clearly' => true],
            'is_verified' => false,
            'collected_at' => now()->toDateString(),
        ];

        DoctorRatingSubmission::create($payload);

        $this->expectException(QueryException::class);
        DoctorRatingSubmission::create($payload);
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
            'offers_second_opinion' => false,
            'offers_whatsapp' => false,
            'status' => DoctorStatus::Draft,
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
            'slug' => 'dhaka',
            'distance_tier' => 'local',
            'has_cancer_center' => true,
        ]);
    }

    private function makeCancerType(): CancerType
    {
        return CancerType::create([
            'name_bn' => 'স্তন',
            'name_en' => 'Breast Cancer',
            'slug' => 'breast-cancer',
            'icon' => 'ribbon',
            'color_key' => 'pink',
            'short_description_bn' => 'বিবরণ',
            'gender_bias' => 'female',
            'is_common' => true,
            'doctor_count_cache' => 0,
            'hospital_count_cache' => 0,
            'guide_published' => false,
            'sort_order' => 1,
        ]);
    }
}
