<?php

namespace App\Http\Controllers;

use App\Enums\PatientCaseStatus;
use App\Models\CancerType;
use App\Models\District;
use App\Models\PatientCase;
use Illuminate\Http\Request;

class PatientCaseController extends Controller
{
    /**
     * Display the public directory of verified patient cases.
     */
    public function index(Request $request)
    {
        $query = PatientCase::query()
            ->published()
            ->with(['cancerType', 'district', 'verifications']);

        // Filtering
        if ($request->filled('cancer')) {
            $cancerSlug = $request->query('cancer');
            $query->whereHas('cancerType', fn ($q) => $q->where('slug', $cancerSlug));
        }

        if ($request->filled('district')) {
            $districtSlug = $request->query('district');
            $query->whereHas('district', fn ($q) => $q->where('slug', $districtSlug));
        }

        // Sorting
        $sort = $request->query('sort', 'recent');
        switch ($sort) {
            case 'urgent':
                $query->orderBy('expires_at', 'asc')->orderBy('id', 'desc');
                break;
            case 'amount_desc':
                $query->orderBy('amount_needed', 'desc');
                break;
            case 'amount_asc':
                $query->orderBy('amount_needed', 'asc');
                break;
            case 'recent':
            default:
                $query->orderBy('verified_at', 'desc')->orderBy('id', 'desc');
                break;
        }

        $cases = $query->paginate(12)->withQueryString();
        $cancerTypes = CancerType::orderBy('sort_order')->orderBy('name_bn')->get();
        $districts = District::orderBy('name_bn')->get();

        return view('patients.index', compact('cases', 'cancerTypes', 'districts', 'sort'));
    }

    /**
     * Display detailed verified case profile.
     */
    public function show(string $case_code)
    {
        $patientCase = PatientCase::query()
            ->where('case_code', $case_code)
            ->where('status', PatientCaseStatus::Published)
            ->with([
                'cancerType',
                'district',
                'hospital',
                'verifications.completedBy',
                'costs' => fn ($q) => $q->orderBy('sort_order', 'asc'),
                'accounts' => fn ($q) => $q->where('is_active', true)->where('name_verified', true),
                'documents' => fn ($q) => $q->where('is_public', true),
                'updates' => fn ($q) => $q->where('is_public', true)->orderBy('update_date', 'desc'),
            ])
            ->firstOrFail();

        return view('patients.show', compact('patientCase'));
    }

    /**
     * Display information page on how patients can apply for verification and support.
     */
    public function apply()
    {
        return view('patients.apply');
    }
}
