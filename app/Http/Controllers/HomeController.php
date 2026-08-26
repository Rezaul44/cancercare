<?php

namespace App\Http\Controllers;

use App\Models\CancerType;
use App\Models\Doctor;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index(): View
    {
        $cancerTypes = Cache::remember('home_cancer_types', 3600, function () {
            return CancerType::orderBy('sort_order')->get();
        });

        $commonCancerTypes = $cancerTypes->where('is_common', true);
        $otherCancerTypes = $cancerTypes->where('is_common', false);

        $totalDoctorsCount = Cache::remember('total_published_doctors_count', 1800, function () {
            return Doctor::published()->count();
        });

        return view('pages.home', [
            'cancerTypes' => $cancerTypes,
            'commonCancerTypes' => $commonCancerTypes,
            'otherCancerTypes' => $otherCancerTypes,
            'totalDoctorsCount' => $totalDoctorsCount,
        ]);
    }
}
