<?php

namespace Tests\Feature;

use App\Models\CancerType;
use App\Models\CostBaseRate;
use App\Models\User;
use Database\Seeders\CancerTypeSeeder;
use Database\Seeders\CostEstimatorSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CostEstimatorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        (new RolePermissionSeeder)->run();
        (new CancerTypeSeeder)->run();
        (new CostEstimatorSeeder)->run();
    }

    public function test_cost_estimator_page_is_accessible_and_renders_initial_data(): void
    {
        $response = $this->get(route('cost-estimator.index'));

        $response->assertOk();
        $response->assertSee('চিকিৎসায় আনুমানিক কত লাগতে পারে');
        $response->assertSee('আপনার তথ্য দিন');
        $response->assertSee('ক্যান্সারের ধরন');
        $response->assertSee('হাসপাতালের ধরন বদলে দেখুন');
        $response->assertSee('কোন ধাপে কত খরচ');
        $response->assertSee('মাসে কত টাকা লাগতে পারে');
        $response->assertSee('আনুষঙ্গিক খরচ — যা সবাই ভুলে যায়');
        $response->assertSee('হিসাবটি প্রিন্ট করুন');
    }

    public function test_cost_estimator_stateless_ajax_endpoint_returns_json_calculation(): void
    {
        $payload = [
            'type' => 'breast',
            'stage' => '2',
            'dist' => 'far',
            'att' => 2,
            'loss' => 'yes',
            'hosp' => 'govt',
            'treat' => [
                'surgery' => true,
                'chemo' => true,
                'radiation' => true,
                'targeted' => false,
            ],
        ];

        $response = $this->postJson(route('ajax.cost.estimate'), $payload);

        $response->assertOk();
        $response->assertJsonStructure([
            'inputs',
            'cancer_type_name_bn',
            'months',
            'trips',
            'direct_cost',
            'direct_percent',
            'indirect_cost',
            'indirect_percent',
            'total_estimated',
            'min_range',
            'max_range',
            'monthly_avg',
            'comparisons' => [
                'govt',
                'npo',
                'priv',
            ],
            'phases',
            'monthly_flow',
            'indirect_items',
        ]);

        $data = $response->json();
        $this->assertGreaterThan(0, $data['total_estimated']);
        $this->assertEquals($data['direct_cost'] + $data['indirect_cost'], $data['total_estimated']);
        $this->assertLessThan($data['max_range'], $data['min_range']);
    }

    public function test_cost_estimator_calculates_all_three_hospital_tiers_accurately(): void
    {
        $payload = [
            'type' => 'lung',
            'stage' => '3',
            'dist' => 'near',
            'att' => 1,
            'loss' => 'no',
            'hosp' => 'govt',
            'treat' => [
                'surgery' => true,
                'chemo' => true,
                'radiation' => true,
                'targeted' => false,
            ],
        ];

        $response = $this->postJson(route('ajax.cost.estimate'), $payload);

        $response->assertOk();
        $comparisons = $response->json('comparisons');

        $this->assertArrayHasKey('govt', $comparisons);
        $this->assertArrayHasKey('npo', $comparisons);
        $this->assertArrayHasKey('priv', $comparisons);

        // Govt cost < NPO cost < Private cost
        $this->assertLessThan($comparisons['npo']['amount'], $comparisons['govt']['amount']);
        $this->assertLessThan($comparisons['priv']['amount'], $comparisons['npo']['amount']);
    }

    public function test_cost_estimator_updates_when_database_rates_change(): void
    {
        $breastCancer = CancerType::where('slug', 'breast-cancer')->first();
        $this->assertNotNull($breastCancer);

        $chemoRate = CostBaseRate::where('cancer_type_id', $breastCancer->id)
            ->where('service_key', 'chemo')
            ->first();
        $this->assertNotNull($chemoRate);

        $payload = [
            'type' => 'breast',
            'stage' => '2',
            'dist' => 'far',
            'att' => 2,
            'loss' => 'yes',
            'hosp' => 'govt',
            'treat' => [
                'surgery' => false,
                'chemo' => true,
                'radiation' => false,
                'targeted' => false,
            ],
        ];

        $res1 = $this->postJson(route('ajax.cost.estimate'), $payload);
        $res1->assertOk();
        $directCost1 = $res1->json('direct_cost');

        // Update rate in database
        $chemoRate->update(['govt_amount' => $chemoRate->govt_amount + 50000]);

        $res2 = $this->postJson(route('ajax.cost.estimate'), $payload);
        $res2->assertOk();
        $directCost2 = $res2->json('direct_cost');

        // Direct cost must increase by 50,000
        $this->assertEquals($directCost1 + 50000, $directCost2);
    }

    public function test_cost_rate_filament_resources_accessible_to_super_admin(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $this->actingAs($superAdmin);

        $response = $this->get('/admin/cost-rates');
        $response->assertOk();

        $responseMultipliers = $this->get('/admin/cost-multipliers');
        $responseMultipliers->assertOk();

        $responseIndirect = $this->get('/admin/cost-indirect-rates');
        $responseIndirect->assertOk();

        $responsePhaseTemplates = $this->get('/admin/cost-phase-templates');
        $responsePhaseTemplates->assertOk();
    }
}
