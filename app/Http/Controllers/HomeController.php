<?php

namespace App\Http\Controllers;

use App\Models\CancerType;
use App\Models\District;
use App\Models\Doctor;
use App\Models\DoctorPatientStory;
use App\Models\Hospital;
use App\Models\PatientCase;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index(): View
    {
        $cancerTypes = Cache::remember('home_cancer_types', 1800, function () {
            return CancerType::withCount(['doctors' => fn ($q) => $q->published()])
                ->orderBy('sort_order')
                ->get();
        });

        $commonCancerTypes = $cancerTypes->where('is_common', true);
        $otherCancerTypes = $cancerTypes->where('is_common', false);

        $totalDoctorsCount = Cache::remember('total_published_doctors_count', 1800, function () {
            return Doctor::published()->count();
        });

        $totalHospitalsCount = Cache::remember('total_published_hospitals_count', 1800, function () {
            return Hospital::published()->count();
        });

        $totalDistrictsCount = Cache::remember('total_districts_count', 86400, function () {
            return District::count();
        });

        $activePatientsCount = Cache::remember('active_patients_count', 1800, function () {
            return PatientCase::published()->count();
        });

        $totalHelpedFamiliesCount = Cache::remember('total_helped_families_count', 1800, function () {
            return (int) Doctor::sum('patients_treated');
        });

        $stories = Cache::remember('home_patient_stories', 1800, function () {
            return DoctorPatientStory::with(['doctor', 'cancerType', 'district'])
                ->orderBy('sort_order')
                ->take(3)
                ->get();
        });

        return view('pages.home', [
            'cancerTypes' => $cancerTypes,
            'commonCancerTypes' => $commonCancerTypes,
            'otherCancerTypes' => $otherCancerTypes,
            'totalDoctorsCount' => $totalDoctorsCount,
            'totalHospitalsCount' => $totalHospitalsCount,
            'totalDistrictsCount' => $totalDistrictsCount,
            'activePatientsCount' => $activePatientsCount,
            'totalHelpedFamiliesCount' => $totalHelpedFamiliesCount,
            'stories' => $stories,
        ]);
    }
}
