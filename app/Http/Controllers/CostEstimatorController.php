<?php

namespace App\Http\Controllers;

use App\Models\CancerType;
use App\Services\CostEstimatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CostEstimatorController extends Controller
{
    public function index(CostEstimatorService $service): View
    {
        $cancerTypes = CancerType::has('costBaseRates')->orderBy('sort_order')->get();

        $defaultInputs = [
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

        $initialEstimate = $service->estimate($defaultInputs);

        return view('pages.cost-estimator', [
            'cancerTypes' => $cancerTypes,
            'initialEstimate' => $initialEstimate,
        ]);
    }

    /**
     * Stateless calculation endpoint — strictly preserves user privacy.
     * No inputs or results are stored in the database.
     */
    public function calculate(Request $request, CostEstimatorService $service): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'stage' => 'required|string|in:1,2,3,4',
            'dist' => 'required|string|in:local,near,far',
            'att' => 'required|numeric|in:1,2',
            'loss' => 'required|string|in:yes,no',
            'hosp' => 'required|string|in:govt,npo,priv,private',
            'treat' => 'nullable|array',
            'treat.surgery' => 'nullable|boolean',
            'treat.chemo' => 'nullable|boolean',
            'treat.radiation' => 'nullable|boolean',
            'treat.targeted' => 'nullable|boolean',
        ]);

        $estimate = $service->estimate($validated);

        return response()->json($estimate);
    }
}
