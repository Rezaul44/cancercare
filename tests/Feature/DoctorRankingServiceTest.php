<?php

namespace Tests\Feature;

use App\Enums\DoctorStatus;
use App\Models\CancerType;
use App\Models\Chamber;
use App\Models\District;
use App\Models\Doctor;
use App\Models\DoctorRatingSummary;
use App\Services\DoctorRankingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * docs/CLAUDE.md "নীতি ১" (র‍্যাঙ্কিং কেনা যায় না) এবং docs/CCB_prompt_playbook.md §2.4-এর
 * ফর্মুলা অনুযায়ী DoctorRankingService যাচাই করে — সবচেয়ে গুরুত্বপূর্ণ: নিষিদ্ধ টেবিল/কলাম
 * (doctor_videos, payments, is_paid_production, হাসপাতালের খ্যাতি) কখনো query-তে আসে না।
 */
class DoctorRankingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_ranking_query_never_references_forbidden_tables_or_columns(): void
    {
        $service = new DoctorRankingService;

        $builder = $service->query(['cancer_type_id' => 1, 'district_id' => 1]);
        $sql = strtolower($builder->toSql());

        foreach ([
            'doctor_videos',
            'payments',
            'is_paid_production',
            'hospitals',
            'hospital_id',
            'success_rate',
            'priority',
            'featured',
        ] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $sql, "Ranking query must never reference '{$forbidden}'.");
        }

        $joins = $builder->getQuery()->joins ?? [];
        $this->assertCount(1, $joins, 'Ranking query should only join doctor_rating_summaries.');
        $this->assertStringContainsString('doctor_rating_summaries', $joins[0]->table);
    }

    public function test_query_builder_is_not_ordered(): void
    {
        $service = new DoctorRankingService;

        $sql = strtolower($service->query()->toSql());

        $this->assertStringNotContainsString('order by', $sql);
    }

    public function test_weights_are_read_from_settings_table_not_hardcoded(): void
    {
        $this->seedWeights(['primary' => 40, 'secondary' => 20, 'district' => 25, 'rating' => 15, 'availability' => 10]);

        $ratingStrong = $this->makeDoctor(['name_en' => 'Rating Strong', 'bmdc_number' => 'W-1']);
        DoctorRatingSummary::create([
            'doctor_id' => $ratingStrong->id,
            'total_count' => 20,
            'criteria_scores' => [],
            'overall_score' => 5.0,
            'is_published' => true,
            'last_calculated_at' => now(),
        ]);

        $availabilityStrong = $this->makeDoctor(['name_en' => 'Availability Strong', 'bmdc_number' => 'W-2']);
        $district = $this->makeDistrict();
        Chamber::create([
            'doctor_id' => $availabilityStrong->id,
            'name_bn' => 'চেম্বার', 'address_bn' => 'ঠিকানা', 'district_id' => $district->id,
            'type' => 'private', 'fee' => 500, 'days_bn' => 'রবি', 'time_from' => '09:00', 'time_to' => '13:00',
            'avg_wait_minutes' => 0, 'is_active' => true,
        ]);

        $service = new DoctorRankingService;

        // ডিফল্ট ওয়েটে rating(15) &gt; availability(10) — rating-strong ডাক্তার প্রথমে।
        $first = $service->rank()->items()[0];
        $this->assertTrue($first->is($ratingStrong));

        // settings টেবিলে ওয়েট উল্টে দিলে অর্ডারও উল্টে যাওয়া উচিত — প্রমাণ করে হার্ডকোড নয়।
        $this->seedWeights(['primary' => 40, 'secondary' => 20, 'district' => 25, 'rating' => 0, 'availability' => 100]);

        $firstAfterFlip = $service->rank()->items()[0];
        $this->assertTrue($firstAfterFlip->is($availabilityStrong));
    }

    public function test_primary_specialty_match_outranks_secondary_match(): void
    {
        $this->seedWeights();
        $cancerType = $this->makeCancerType();

        $primaryMatch = $this->makeDoctor(['name_en' => 'Primary Match', 'bmdc_number' => 'S-1']);
        $primaryMatch->cancerTypes()->attach($cancerType->id, ['is_primary' => true]);

        $secondaryMatch = $this->makeDoctor(['name_en' => 'Secondary Match', 'bmdc_number' => 'S-2']);
        $secondaryMatch->cancerTypes()->attach($cancerType->id, ['is_primary' => false]);

        $service = new DoctorRankingService;
        $results = $service->rank(['cancer_type_id' => $cancerType->id]);

        $this->assertTrue($results->items()[0]->is($primaryMatch));
        $this->assertTrue($results->items()[1]->is($secondaryMatch));
    }

    public function test_unpublished_rating_summary_contributes_zero_not_penalty(): void
    {
        $this->seedWeights();

        $unpublished = $this->makeDoctor(['name_en' => 'Unpublished High Rating', 'bmdc_number' => 'R-1']);
        DoctorRatingSummary::create([
            'doctor_id' => $unpublished->id,
            'total_count' => 3,
            'criteria_scores' => [],
            'overall_score' => 4.9,
            'is_published' => false,
            'last_calculated_at' => now(),
        ]);

        $noSummary = $this->makeDoctor(['name_en' => 'No Rating At All', 'bmdc_number' => 'R-2']);

        $service = new DoctorRankingService;
        $doctors = $service->query()->get()->keyBy('id');

        $this->assertSame(0.0, (float) $doctors[$unpublished->id]->rating_score);
        $this->assertSame(0.0, (float) $doctors[$noSummary->id]->rating_score);
        $this->assertSame((float) $doctors[$noSummary->id]->score, (float) $doctors[$unpublished->id]->score);
    }

    public function test_rotation_seed_tie_break_orders_equal_score_doctors(): void
    {
        $this->seedWeights();

        $doctorA = $this->makeDoctor(['name_en' => 'Rotation A', 'bmdc_number' => 'T-1', 'rotation_seed' => 100]);
        $doctorB = $this->makeDoctor(['name_en' => 'Rotation B', 'bmdc_number' => 'T-2', 'rotation_seed' => 900]);

        $week = now()->weekOfYear;
        $expectedFirst = (($doctorA->rotation_seed + $week) % 1000) <= (($doctorB->rotation_seed + $week) % 1000)
            ? $doctorA
            : $doctorB;

        $service = new DoctorRankingService;
        $first = $service->rank()->items()[0];

        $this->assertTrue($first->is($expectedFirst));
    }

    public function test_district_and_cancer_type_filters_hard_exclude_non_matching_doctors(): void
    {
        $this->seedWeights();
        $cancerType = $this->makeCancerType();
        $district = $this->makeDistrict();

        $matching = $this->makeDoctor(['name_en' => 'Matching Doctor', 'bmdc_number' => 'F-1']);
        $matching->cancerTypes()->attach($cancerType->id, ['is_primary' => true]);
        Chamber::create([
            'doctor_id' => $matching->id,
            'name_bn' => 'চেম্বার', 'address_bn' => 'ঠিকানা', 'district_id' => $district->id,
            'type' => 'private', 'fee' => 500, 'days_bn' => 'রবি', 'time_from' => '09:00', 'time_to' => '13:00',
            'is_active' => true,
        ]);

        // উচ্চ রেটিং থাকলেও cancer type ম্যাচ না করলে বাদ পড়া উচিত।
        $wrongSpecialty = $this->makeDoctor(['name_en' => 'Wrong Specialty', 'bmdc_number' => 'F-2']);
        DoctorRatingSummary::create([
            'doctor_id' => $wrongSpecialty->id, 'total_count' => 20, 'criteria_scores' => [],
            'overall_score' => 5.0, 'is_published' => true, 'last_calculated_at' => now(),
        ]);

        // ম্যাচিং specialty থাকলেও ওই জেলায় চেম্বার না থাকলে বাদ পড়া উচিত।
        $wrongDistrict = $this->makeDoctor(['name_en' => 'Wrong District', 'bmdc_number' => 'F-3']);
        $wrongDistrict->cancerTypes()->attach($cancerType->id, ['is_primary' => true]);

        $service = new DoctorRankingService;
        $ids = $service->query(['cancer_type_id' => $cancerType->id, 'district_id' => $district->id])
            ->get()
            ->pluck('id')
            ->all();

        $this->assertSame([$matching->id], $ids);
    }

    public function test_availability_score_reflects_wait_time(): void
    {
        $this->seedWeights(['primary' => 40, 'secondary' => 20, 'district' => 25, 'rating' => 15, 'availability' => 10]);
        $district = $this->makeDistrict();

        $noChamber = $this->makeDoctor(['name_en' => 'No Chamber', 'bmdc_number' => 'A-1']);

        $unknownWait = $this->makeDoctor(['name_en' => 'Unknown Wait', 'bmdc_number' => 'A-2']);
        $this->makeChamber($unknownWait, $district, null);

        $lowWait = $this->makeDoctor(['name_en' => 'Low Wait', 'bmdc_number' => 'A-3']);
        $this->makeChamber($lowWait, $district, 0);

        $highWait = $this->makeDoctor(['name_en' => 'High Wait', 'bmdc_number' => 'A-4']);
        $this->makeChamber($highWait, $district, 150);

        $service = new DoctorRankingService;
        $doctors = $service->query()->get()->keyBy('id');

        $this->assertSame(0.0, (float) $doctors[$noChamber->id]->availability_score);
        $this->assertSame(5.0, (float) $doctors[$unknownWait->id]->availability_score);
        $this->assertSame(10.0, (float) $doctors[$lowWait->id]->availability_score);
        $this->assertSame(0.0, (float) $doctors[$highWait->id]->availability_score);
    }

    /**
     * @param  array<string, int>  $weights
     */
    private function seedWeights(array $weights = ['primary' => 40, 'secondary' => 20, 'district' => 25, 'rating' => 15, 'availability' => 10]): void
    {
        DB::table('settings')->updateOrInsert(
            ['key' => 'ranking.weights'],
            ['value' => json_encode($weights), 'group' => 'ranking', 'updated_at' => now()]
        );
    }

    private function makeDoctor(array $overrides = []): Doctor
    {
        return Doctor::create(array_merge([
            'name_bn' => 'ডা. পরীক্ষা',
            'name_en' => 'Test Doctor '.uniqid(),
            'bmdc_number' => 'BMDC-'.uniqid(),
            'photo_path' => 'doctors/photo.jpg',
            'degrees_line_bn' => 'এমবিবিএস',
            'experience_years' => 10,
            'current_position_bn' => 'কনসালট্যান্ট',
            'gender' => 'female',
            'status' => DoctorStatus::Published,
            'doctor_approved_at' => now()->subMonth(),
        ], $overrides));
    }

    private function makeChamber(Doctor $doctor, District $district, ?int $avgWaitMinutes): Chamber
    {
        return Chamber::create([
            'doctor_id' => $doctor->id,
            'name_bn' => 'চেম্বার',
            'address_bn' => 'ঠিকানা',
            'district_id' => $district->id,
            'type' => 'private',
            'fee' => 500,
            'days_bn' => 'রবি',
            'time_from' => '09:00',
            'time_to' => '13:00',
            'avg_wait_minutes' => $avgWaitMinutes,
            'is_active' => true,
        ]);
    }

    private function makeDistrict(): District
    {
        $divisionId = DB::table('divisions')->insertGetId([
            'name_bn' => 'ঢাকা', 'name_en' => 'Dhaka', 'slug' => 'dhaka-'.uniqid(),
        ]);

        return District::create([
            'division_id' => $divisionId,
            'name_bn' => 'ঢাকা', 'name_en' => 'Dhaka', 'slug' => 'dhaka-'.uniqid(),
            'distance_tier' => 'local', 'has_cancer_center' => true,
        ]);
    }

    private function makeCancerType(): CancerType
    {
        return CancerType::create([
            'name_bn' => 'স্তন', 'name_en' => 'Breast Cancer', 'slug' => 'breast-cancer-'.uniqid(),
            'icon' => 'ribbon', 'color_key' => 'pink', 'short_description_bn' => 'বিবরণ',
            'is_common' => true, 'doctor_count_cache' => 0, 'hospital_count_cache' => 0,
            'guide_published' => false, 'sort_order' => 1,
        ]);
    }
}
