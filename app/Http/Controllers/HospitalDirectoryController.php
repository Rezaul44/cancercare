<?php

namespace App\Http\Controllers;

use App\Enums\HospitalCapabilityStatus;
use App\Enums\HospitalStatus;
use App\Enums\HospitalType;
use App\Models\Capability;
use App\Models\District;
use App\Models\Division;
use App\Models\Hospital;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HospitalDirectoryController extends Controller
{
    private const SORTS = ['relevance', 'cost_low', 'wait_low', 'name'];

    public function index(Request $request): View
    {
        $filters = $this->resolveFilters($request);
        $sort = in_array($request->query('sort'), self::SORTS, true) ? $request->query('sort') : 'relevance';

        $query = Hospital::query()
            ->published()
            ->with([
                'district.division',
                'capabilities.capability',
                'waitTimes' => fn ($q) => $q->orderBy('sort_order'),
                'videos',
                'prepInfos',
                'practicalInfos',
            ]);

        // 1. Filter: Capabilities (যে চিকিৎসা দরকার)
        if (! empty($filters['capabilities'])) {
            foreach ($filters['capabilities'] as $capKey) {
                $query->whereHas('capabilities', function ($q) use ($capKey) {
                    $q->whereHas('capability', fn ($cq) => $cq->where('key', $capKey))
                        ->where('status', '!=', HospitalCapabilityStatus::NotAvailable->value);
                });
            }
        }

        // 2. Filter: Hospital Types (ধরন)
        if (! empty($filters['types'])) {
            $query->whereIn('type', $filters['types']);
        }

        // 3. Filter: Divisions (বিভাগ)
        if (! empty($filters['division_ids'])) {
            $query->whereHas('district', function ($q) use ($filters) {
                $q->whereIn('division_id', $filters['division_ids']);
            });
        }

        // 4. Filter: District (জেলা)
        if (! empty($filters['district_id'])) {
            $query->where('district_id', $filters['district_id']);
        }

        // 5. Filter: Facilities (সুবিধা)
        if (! empty($filters['facilities'])) {
            foreach ($filters['facilities'] as $facility) {
                match ($facility) {
                    'emergency_24h' => $query->where('emergency_24h', true),
                    'has_video' => $query->has('videos'),
                    'has_financial_aid' => $query->whereHas('practicalInfos', fn ($q) => $q->where('key', 'financial_aid')),
                    'has_accommodation' => $query->whereHas('practicalInfos', fn ($q) => $q->where('key', 'accommodation')),
                    'has_blood_bank' => $query->whereHas('capabilities', fn ($q) => $q->whereHas('capability', fn ($cq) => $cq->where('key', 'blood_bank'))->where('status', '!=', HospitalCapabilityStatus::NotAvailable->value)),
                    'has_female_oncologist' => $query->whereHas('capabilities', fn ($q) => $q->whereHas('capability', fn ($cq) => $cq->where('key', 'female_oncologist'))->where('status', '!=', HospitalCapabilityStatus::NotAvailable->value)),
                    default => null,
                };
            }
        }

        // Sorting
        match ($sort) {
            'cost_low' => $query->orderByRaw('outdoor_fee IS NULL, outdoor_fee ASC'),
            'name' => $query->orderBy('name_bn', 'asc'),
            'wait_low' => $query->leftJoin('hospital_wait_times', function ($join) {
                $join->on('hospitals.id', '=', 'hospital_wait_times.hospital_id')
                    ->where('hospital_wait_times.service_key', '=', 'first_visit');
            })->select('hospitals.*')->orderByRaw('hospital_wait_times.min_weeks IS NULL, hospital_wait_times.min_weeks ASC'),
            default => $query->orderByRaw('cover_photo_path IS NOT NULL DESC, bed_count DESC, id ASC'),
        };

        $hospitals = $query->paginate(15)->withQueryString();

        // Filter Counts (cached to prevent 30+ database queries on every request)
        $allCapabilities = \Illuminate\Support\Facades\Cache::remember('all_capabilities_sorted', 3600, fn () => Capability::orderBy('sort_order')->get());

        $sidebarData = \Illuminate\Support\Facades\Cache::remember('hospital_sidebar_counts', 1800, function () use ($allCapabilities) {
            $capabilityCounts = [];
            foreach ($allCapabilities as $cap) {
                $capabilityCounts[$cap->key] = Hospital::published()
                    ->whereHas('capabilities', fn ($q) => $q->where('capability_id', $cap->id)->where('status', '!=', HospitalCapabilityStatus::NotAvailable->value))
                    ->count();
            }

            $typeCounts = [
                'govt' => Hospital::published()->govt()->count(),
                'private' => Hospital::published()->private()->count(),
                'npo' => Hospital::published()->npo()->count(),
            ];

            $divisions = Division::all();
            $divisionCounts = [];
            foreach ($divisions as $div) {
                $divisionCounts[$div->id] = Hospital::published()
                    ->whereHas('district', fn ($q) => $q->where('division_id', $div->id))
                    ->count();
            }

            $facilityCounts = [
                'emergency_24h' => Hospital::published()->where('emergency_24h', true)->count(),
                'has_video' => Hospital::published()->has('videos')->count(),
                'has_financial_aid' => Hospital::published()->whereHas('practicalInfos', fn ($q) => $q->where('key', 'financial_aid'))->count(),
                'has_accommodation' => Hospital::published()->whereHas('practicalInfos', fn ($q) => $q->where('key', 'accommodation'))->count(),
                'has_blood_bank' => $capabilityCounts['blood_bank'] ?? 0,
                'has_female_oncologist' => $capabilityCounts['female_oncologist'] ?? 0,
            ];

            $totalPublishedHospitals = Hospital::published()->count();

            return [
                'capabilityCounts' => $capabilityCounts,
                'typeCounts' => $typeCounts,
                'divisions' => $divisions,
                'divisionCounts' => $divisionCounts,
                'facilityCounts' => $facilityCounts,
                'totalPublishedHospitals' => $totalPublishedHospitals,
            ];
        });

        // Dynamic result heading text
        $resultHeading = 'মোট '.$hospitals->total().'টি হাসপাতাল পাওয়া গেছে';
        if (! empty($filters['capabilities'])) {
            $firstCap = $allCapabilities->firstWhere('key', $filters['capabilities'][0]);
            if ($firstCap) {
                $resultHeading = "<b>{$hospitals->total()}টি</b> হাসপাতালে {$firstCap->label_bn} সুবিধা আছে";
            }
        }

        $data = [
            'hospitals' => $hospitals,
            'sort' => $sort,
            'filters' => $filters,
            'allCapabilities' => $allCapabilities,
            'capabilityCounts' => $sidebarData['capabilityCounts'],
            'typeCounts' => $sidebarData['typeCounts'],
            'divisions' => $sidebarData['divisions'],
            'divisionCounts' => $sidebarData['divisionCounts'],
            'facilityCounts' => $sidebarData['facilityCounts'],
            'resultHeading' => $resultHeading,
            'totalPublishedHospitals' => $sidebarData['totalPublishedHospitals'],
        ];

        if ($request->ajax()) {
            return view('pages.hospitals._results', $data);
        }

        return view('pages.hospitals.index', $data);
    }

    /**
     * @return array{
     *     capabilities: list<string>,
     *     types: list<string>,
     *     division_ids: list<int>,
     *     district_id: int|null,
     *     facilities: list<string>
     * }
     */
    private function resolveFilters(Request $request): array
    {
        $capabilities = (array) $request->query('capabilities', []);
        if ($request->has('capability') && ! $request->has('capabilities')) {
            $capabilities = (array) $request->query('capability');
        }
        $capabilities = array_values(array_filter($capabilities, 'is_string'));

        $types = (array) $request->query('types', []);
        if ($request->has('type') && ! $request->has('types')) {
            $types = (array) $request->query('type');
        }
        $validTypes = [HospitalType::Govt->value, HospitalType::Private->value, HospitalType::Npo->value];
        $types = array_values(array_intersect($types, $validTypes));

        $divisionIds = (array) $request->query('division_ids', []);
        if ($request->has('division_id') && ! $request->has('division_ids')) {
            $divisionIds = (array) $request->query('division_id');
        }
        $divisionIds = array_values(array_map('intval', array_filter($divisionIds, 'is_numeric')));

        $districtId = $request->query('district_id');
        $districtId = is_numeric($districtId) ? (int) $districtId : null;

        $facilities = (array) $request->query('facilities', []);
        if ($request->has('facility') && ! $request->has('facilities')) {
            $facilities = (array) $request->query('facility');
        }
        $validFacilities = ['emergency_24h', 'has_video', 'has_financial_aid', 'has_accommodation', 'has_blood_bank', 'has_female_oncologist'];
        $facilities = array_values(array_intersect($facilities, $validFacilities));

        return [
            'capabilities' => $capabilities,
            'types' => $types,
            'division_ids' => $divisionIds,
            'district_id' => $districtId,
            'facilities' => $facilities,
        ];
    }
}
