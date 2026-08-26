<?php

namespace Tests\Feature;

use App\Enums\DoctorStatus;
use App\Models\CancerType;
use App\Models\Chamber;
use App\Models\District;
use App\Models\Doctor;
use App\Models\DoctorRatingSummary;
use App\Models\DoctorType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * /doctors পাতা — docs/prototypes/doctor_directory.html-এর "ডাক্তার খুঁজছি" অংশ। ফিল্টার/সর্ট/AJAX
 * partial/pagination বাস্তব route দিয়ে যাচাই করে (DoctorRankingServiceTest সার্ভিস-স্তর ইতিমধ্যে ঢেকেছে)।
 */
class DoctorDirectoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedWeights();
    }

    public function test_full_page_renders_dropdowns_sidebar_and_disclosures(): void
    {
        $response = $this->get(route('doctors.index'));

        $response->assertOk();
        $response->assertSee('যাচাই করা অনকোলজিস্ট');
        $response->assertSee('ক্যান্সারের ধরন');
        $response->assertSee('জেলা');
        $response->assertSee('ডাক্তারের ধরন');
        $response->assertSee('হাসপাতালের ধরন');
        $response->assertSee('ভিজিট ফি');
        $response->assertSee('ডাক্তারের লিঙ্গ');
        $response->assertSee('সুবিধা');
        $response->assertDontSee('এই সপ্তাহে খালি');
        $response->assertSee('প্রাসঙ্গিকতা');
        $response->assertSee('নিকটতম');
        $response->assertSee('কম ফি');
        $response->assertSee('রেটিং');
        $response->assertSee('দ্রুত সময়');
        $response->assertSee('কোনো ডাক্তার বা হাসপাতাল টাকা দিয়ে উপরে আসতে পারে না');
        $response->assertSee('সমান যোগ্যতার ডাক্তারদের ক্রম নিয়মিত ঘোরানো হয়');
    }

    public function test_ajax_request_returns_only_the_results_fragment(): void
    {
        $this->makeDoctor();

        $response = $this->withHeader('X-Requested-With', 'XMLHttpRequest')->get(route('doctors.index'));

        $response->assertOk();
        $response->assertDontSee('<html', false);
        $response->assertDontSee('<nav', false);
        $response->assertSee('প্রাসঙ্গিকতা');
    }

    public function test_doctor_type_filter_excludes_non_matching_doctors(): void
    {
        $surgical = $this->makeDoctorType('surgical', 'সার্জিক্যাল');
        $medical = $this->makeDoctorType('medical', 'মেডিকেল');

        $matching = $this->makeDoctor(['bmdc_number' => 'DT-1']);
        $matching->doctorTypes()->attach($surgical->id);

        $nonMatching = $this->makeDoctor(['bmdc_number' => 'DT-2']);
        $nonMatching->doctorTypes()->attach($medical->id);

        $response = $this->get(route('doctors.index', ['doctor_type' => ['surgical']]));

        $response->assertOk();
        $response->assertSee($matching->name_bn);
        $response->assertDontSee($nonMatching->bmdc_number);
    }

    public function test_hospital_type_filter_excludes_non_matching_doctors(): void
    {
        $district = $this->makeDistrict();

        $govt = $this->makeDoctor(['bmdc_number' => 'HT-1']);
        $this->makeChamber($govt, $district, ['type' => 'govt']);

        $private = $this->makeDoctor(['bmdc_number' => 'HT-2']);
        $this->makeChamber($private, $district, ['type' => 'private']);

        $response = $this->get(route('doctors.index', ['hospital_type' => ['govt']]));

        $response->assertOk();
        $ids = $this->doctorIdsInResponse($response);

        $this->assertContains($govt->id, $ids);
        $this->assertNotContains($private->id, $ids);
    }

    public function test_fee_bucket_filter_excludes_non_matching_doctors(): void
    {
        $district = $this->makeDistrict();

        $cheap = $this->makeDoctor(['bmdc_number' => 'FB-1']);
        $this->makeChamber($cheap, $district, ['fee' => 300]);

        $expensive = $this->makeDoctor(['bmdc_number' => 'FB-2']);
        $this->makeChamber($expensive, $district, ['fee' => 2000]);

        $response = $this->get(route('doctors.index', ['fee' => ['upto_500']]));

        $ids = $this->doctorIdsInResponse($response);

        $this->assertContains($cheap->id, $ids);
        $this->assertNotContains($expensive->id, $ids);
    }

    public function test_gender_filter_excludes_non_matching_doctors(): void
    {
        $female = $this->makeDoctor(['bmdc_number' => 'G-1', 'gender' => 'female']);
        $male = $this->makeDoctor(['bmdc_number' => 'G-2', 'gender' => 'male']);

        $response = $this->get(route('doctors.index', ['gender' => ['female']]));

        $ids = $this->doctorIdsInResponse($response);

        $this->assertContains($female->id, $ids);
        $this->assertNotContains($male->id, $ids);
    }

    public function test_facility_filter_excludes_non_matching_doctors(): void
    {
        $whatsapp = $this->makeDoctor(['bmdc_number' => 'FC-1', 'offers_whatsapp' => true]);
        $none = $this->makeDoctor(['bmdc_number' => 'FC-2', 'offers_whatsapp' => false, 'offers_second_opinion' => false]);

        $response = $this->get(route('doctors.index', ['facility' => ['whatsapp']]));

        $ids = $this->doctorIdsInResponse($response);

        $this->assertContains($whatsapp->id, $ids);
        $this->assertNotContains($none->id, $ids);
    }

    public function test_multi_select_within_a_group_is_or_combined(): void
    {
        $district = $this->makeDistrict();

        $govt = $this->makeDoctor(['bmdc_number' => 'OR-1']);
        $this->makeChamber($govt, $district, ['type' => 'govt']);

        $private = $this->makeDoctor(['bmdc_number' => 'OR-2']);
        $this->makeChamber($private, $district, ['type' => 'private']);

        $npo = $this->makeDoctor(['bmdc_number' => 'OR-3']);
        $this->makeChamber($npo, $district, ['type' => 'npo']);

        $response = $this->get(route('doctors.index', ['hospital_type' => ['govt', 'private']]));

        $ids = $this->doctorIdsInResponse($response);

        $this->assertContains($govt->id, $ids);
        $this->assertContains($private->id, $ids);
        $this->assertNotContains($npo->id, $ids);
    }

    public function test_chamber_filters_must_match_a_single_chamber_not_scattered_across_chambers(): void
    {
        $dhaka = $this->makeDistrict('dhaka');
        $chattogram = $this->makeDistrict('chattogram');

        // এই ডাক্তারের ঢাকায় একটা govt চেম্বার, চট্টগ্রামে একটা private চেম্বার — কোনোটাই
        // "ঢাকা + private" একসাথে মেলায় না, তাই বাদ পড়া উচিত।
        $scattered = $this->makeDoctor(['bmdc_number' => 'SC-1']);
        $this->makeChamber($scattered, $dhaka, ['type' => 'govt']);
        $this->makeChamber($scattered, $chattogram, ['type' => 'private']);

        $matching = $this->makeDoctor(['bmdc_number' => 'SC-2']);
        $this->makeChamber($matching, $dhaka, ['type' => 'private']);

        $response = $this->get(route('doctors.index', [
            'district' => $dhaka->slug,
            'hospital_type' => ['private'],
        ]));

        $ids = $this->doctorIdsInResponse($response);

        $this->assertContains($matching->id, $ids);
        $this->assertNotContains($scattered->id, $ids);
    }

    public function test_slug_and_key_based_query_params_resolve_correctly(): void
    {
        $cancerType = $this->makeCancerType();
        $district = $this->makeDistrict();

        $matching = $this->makeDoctor(['bmdc_number' => 'SL-1']);
        $matching->cancerTypes()->attach($cancerType->id, ['is_primary' => true]);
        $this->makeChamber($matching, $district);

        $response = $this->get(route('doctors.index', [
            'cancer' => $cancerType->slug,
            'district' => $district->slug,
        ]));

        $response->assertOk();
        $ids = $this->doctorIdsInResponse($response);
        $this->assertSame([$matching->id], $ids);
    }

    public function test_malformed_query_params_do_not_error(): void
    {
        $response = $this->get('/doctors?cancer=not-a-real-slug&district=bogus&hospital_type[]=nonsense&fee[]=made-up&gender[]=alien&sort=not-a-sort-mode');

        $response->assertOk();
    }

    public function test_rating_sort_orders_by_published_rating_regardless_of_ranking_weights(): void
    {
        // rating ওয়েট শূন্য করে দিলেও রেটিং sort ঠিকভাবে কাজ করা উচিত (score/rating_score কলামের
        // ওপর নির্ভর করলে এটা ভেঙে যেত)।
        $this->seedWeights(['primary' => 40, 'secondary' => 20, 'district' => 25, 'rating' => 0, 'availability' => 10]);

        $highRated = $this->makeDoctor(['bmdc_number' => 'RT-1']);
        DoctorRatingSummary::create([
            'doctor_id' => $highRated->id, 'total_count' => 20, 'criteria_scores' => [],
            'overall_score' => 4.9, 'is_published' => true, 'last_calculated_at' => now(),
        ]);

        $lowRated = $this->makeDoctor(['bmdc_number' => 'RT-2']);
        DoctorRatingSummary::create([
            'doctor_id' => $lowRated->id, 'total_count' => 20, 'criteria_scores' => [],
            'overall_score' => 2.0, 'is_published' => true, 'last_calculated_at' => now(),
        ]);

        $noRating = $this->makeDoctor(['bmdc_number' => 'RT-3']);

        $response = $this->get(route('doctors.index', ['sort' => 'rating']));

        $ids = $this->doctorIdsInResponse($response);

        $this->assertSame([$highRated->id, $lowRated->id, $noRating->id], $ids);
    }

    public function test_fee_sort_orders_lowest_first_with_no_chamber_doctors_last(): void
    {
        $district = $this->makeDistrict();

        $cheap = $this->makeDoctor(['bmdc_number' => 'FS-1']);
        $this->makeChamber($cheap, $district, ['fee' => 300]);

        $expensive = $this->makeDoctor(['bmdc_number' => 'FS-2']);
        $this->makeChamber($expensive, $district, ['fee' => 3000]);

        $noChamber = $this->makeDoctor(['bmdc_number' => 'FS-3']);

        $response = $this->get(route('doctors.index', ['sort' => 'fee']));

        $ids = $this->doctorIdsInResponse($response);

        $this->assertSame([$cheap->id, $expensive->id, $noChamber->id], $ids);
    }

    public function test_wait_sort_orders_known_fast_before_unknown_before_no_chamber(): void
    {
        $district = $this->makeDistrict();

        $fast = $this->makeDoctor(['bmdc_number' => 'WS-1']);
        $this->makeChamber($fast, $district, ['avg_wait_minutes' => 5]);

        $unknown = $this->makeDoctor(['bmdc_number' => 'WS-2']);
        $this->makeChamber($unknown, $district, ['avg_wait_minutes' => null]);

        $noChamber = $this->makeDoctor(['bmdc_number' => 'WS-3']);

        $response = $this->get(route('doctors.index', ['sort' => 'wait']));

        $ids = $this->doctorIdsInResponse($response);

        $this->assertSame([$fast->id, $unknown->id, $noChamber->id], $ids);
    }

    public function test_nearest_sort_orders_by_district_distance_tier(): void
    {
        $local = $this->makeDistrict('local-d', 'local');
        $far = $this->makeDistrict('far-d', 'far');

        $nearDoctor = $this->makeDoctor(['bmdc_number' => 'NS-1']);
        $this->makeChamber($nearDoctor, $local);

        $farDoctor = $this->makeDoctor(['bmdc_number' => 'NS-2']);
        $this->makeChamber($farDoctor, $far);

        $noChamber = $this->makeDoctor(['bmdc_number' => 'NS-3']);

        $response = $this->get(route('doctors.index', ['sort' => 'nearest']));

        $ids = $this->doctorIdsInResponse($response);

        $this->assertSame([$nearDoctor->id, $farDoctor->id, $noChamber->id], $ids);
    }

    public function test_pagination_links_preserve_active_filters(): void
    {
        $district = $this->makeDistrict();

        foreach (range(1, 25) as $i) {
            $doctor = $this->makeDoctor(['bmdc_number' => "PG-{$i}"]);
            $this->makeChamber($doctor, $district, ['type' => 'govt']);
        }

        $response = $this->get(route('doctors.index', ['hospital_type' => ['govt']]));

        $response->assertOk();
        // পেজিনেশন লিংকে সক্রিয় ফিল্টার query string হিসেবে থেকে যাওয়া উচিত (withQueryString())।
        $response->assertSee('hospital_type', false);
        $response->assertSee('govt', false);
    }

    public function test_no_n_plus_one_queries_as_result_set_grows(): void
    {
        $district = $this->makeDistrict();
        $cancerType = $this->makeCancerType();

        $makeSet = function (int $count) use ($district, $cancerType) {
            for ($i = 0; $i < $count; $i++) {
                $doctor = $this->makeDoctor(['bmdc_number' => 'NQ-'.uniqid()]);
                $doctor->cancerTypes()->attach($cancerType->id, ['is_primary' => true]);
                $this->makeChamber($doctor, $district);
                DoctorRatingSummary::create([
                    'doctor_id' => $doctor->id, 'total_count' => 20, 'criteria_scores' => [],
                    'overall_score' => 4.5, 'is_published' => true, 'last_calculated_at' => now(),
                ]);
            }
        };

        $makeSet(2);

        DB::enableQueryLog();
        $this->get(route('doctors.index'))->assertOk();
        $smallCount = count(DB::getQueryLog());
        DB::flushQueryLog();

        // flushQueryLog() শুধু লগ খালি করে, লগিং বন্ধ করে না — তাই makeSet(18)-এর INSERT
        // কোয়েরিগুলো গণনায় ঢুকে না যাওয়ার জন্য এখানে আরেকবার flush করা হচ্ছে, ঠিক আগে।
        $makeSet(18);
        DB::flushQueryLog();

        $this->get(route('doctors.index'))->assertOk();
        $largeCount = count(DB::getQueryLog());
        DB::flushQueryLog();

        $this->assertSame($smallCount, $largeCount, 'Query count must not grow with result set size (N+1).');
    }

    public function test_reason_tags_render_for_a_multi_match_doctor(): void
    {
        $cancerType = $this->makeCancerType();
        $district = $this->makeDistrict();

        $doctor = $this->makeDoctor(['bmdc_number' => 'RS-1', 'offers_whatsapp' => true, 'offers_second_opinion' => true]);
        $doctor->cancerTypes()->attach($cancerType->id, ['is_primary' => true]);
        $this->makeChamber($doctor, $district);
        DoctorRatingSummary::create([
            'doctor_id' => $doctor->id, 'total_count' => 20, 'criteria_scores' => [],
            'overall_score' => 4.8, 'is_published' => true, 'last_calculated_at' => now(),
        ]);

        $response = $this->get(route('doctors.index', ['cancer' => $cancerType->slug, 'district' => $district->slug]));

        $response->assertOk();
        $response->assertSee($cancerType->name_bn.'-এ বিশেষত্ব');
        $response->assertSee($district->name_bn.'-এ চেম্বার');
        $response->assertSee('উচ্চ রেটিং');
    }

    /**
     * কার্ডে bmdc_number দেখানো হয় না, শুধু name_bn — তাই সাড়ায় প্রতিটি ডাক্তারের name_bn কোথায়
     * প্রথম দেখা যাচ্ছে তা দিয়ে ক্রম (render order) বের করা হয়, যা sort-mode টেস্টের জন্য দরকার।
     *
     * @return list<int>
     */
    private function doctorIdsInResponse($response): array
    {
        $content = $response->getContent();
        $positions = [];

        foreach (Doctor::query()->get() as $doctor) {
            $position = strpos($content, $doctor->name_bn);

            if ($position !== false) {
                $positions[$doctor->id] = $position;
            }
        }

        asort($positions);

        return array_keys($positions);
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
            'name_bn' => 'ডা. পরীক্ষা '.uniqid(),
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

    private function makeDoctorType(string $key, string $labelBn): DoctorType
    {
        return DoctorType::create(['key' => $key, 'label_bn' => $labelBn, 'label_en' => ucfirst($key)]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function makeChamber(Doctor $doctor, District $district, array $overrides = []): Chamber
    {
        return Chamber::create(array_merge([
            'doctor_id' => $doctor->id,
            'name_bn' => 'চেম্বার',
            'address_bn' => 'ঠিকানা',
            'district_id' => $district->id,
            'type' => 'private',
            'fee' => 500,
            'days_bn' => 'রবি',
            'time_from' => '09:00',
            'time_to' => '13:00',
            'avg_wait_minutes' => null,
            'is_active' => true,
        ], $overrides));
    }

    private function makeDistrict(?string $slugPrefix = null, string $distanceTier = 'local'): District
    {
        $slug = ($slugPrefix ?? 'dhaka').'-'.uniqid();

        $divisionId = DB::table('divisions')->insertGetId([
            'name_bn' => 'ঢাকা', 'name_en' => 'Dhaka', 'slug' => 'division-'.uniqid(),
        ]);

        return District::create([
            'division_id' => $divisionId,
            'name_bn' => 'ঢাকা', 'name_en' => 'Dhaka', 'slug' => $slug,
            'distance_tier' => $distanceTier, 'has_cancer_center' => true,
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
