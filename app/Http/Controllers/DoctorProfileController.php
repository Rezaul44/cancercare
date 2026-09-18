<?php

namespace App\Http\Controllers;

use App\Enums\DoctorStatus;
use App\Models\CancerType;
use App\Models\Doctor;
use App\Models\RatingCriteria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * /doctors/{slug} — docs/prototypes/doctor_profile.html। match() হলো "এই ডাক্তার কি আমার জন্য"
 * ম্যাচ ইঞ্জিনের AJAX endpoint (docs/CCB_system_documentation.md ধারা ২)। prototype-এ এই ডেটা
 * client-side হার্ডকোড করা ছিল (const db = {...}); এখানে doctor_cancer_type_stages ও
 * doctor_treatment_specialties টেবিল থেকে আসে যাতে প্রতিটি ডাক্তারের জন্য আলাদা করে বসানো যায়।
 */
class DoctorProfileController extends Controller
{
    /**
     * @var list<string>
     */
    private const MATCH_STAGES = ['1', '2', '3', '4', 'unknown'];

    /**
     * @var list<string>
     */
    private const MATCH_TREATMENTS = ['surgery', 'chemo', 'radiation', 'hormone', 'unknown'];

    /**
     * @var list<string>
     */
    private const MATCH_CANCER_SLUGS = [
        'breast-cancer', 'cervical-cancer', 'ovarian-cancer', 'lung-cancer', 'blood-cancer', 'stomach-cancer',
    ];

    public function show(Doctor $doctor): View
    {
        abort_unless($doctor->status === DoctorStatus::Published, 404);

        $doctor->load([
            'doctorTypes',
            'cancerTypes',
            'timeline' => fn ($query) => $query->orderBy('sort_order'),
            'services' => fn ($query) => $query->orderBy('sort_order'),
            'philosophyPoints' => fn ($query) => $query->orderBy('sort_order'),
            'videos' => fn ($query) => $query->orderBy('sort_order'),
            'chambers' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order'),
            'chambers.district',
            'chambers.hospital',
            'ratingSummary',
            'patientTestimonials' => fn ($query) => $query->orderBy('sort_order'),
            'patientStories' => fn ($query) => $query->orderBy('sort_order'),
            'storyHighlights' => fn ($query) => $query->orderBy('sort_order'),
        ]);

        $ratingCriteria = Cache::remember('active_rating_criteria', 3600, function () {
            return RatingCriteria::query()->where('is_active', true)->orderBy('sort_order')->get();
        });

        // ম্যাচ ইঞ্জিনের dropdown-এ সবসময় একই তালিকা দেখানো হয় — ডাক্তার যে ক্যান্সারেই বিশেষজ্ঞ হোন
        // না কেন, "উপযুক্ত নন" ফলাফল দেখানোর জন্যও পুরো তালিকা দরকার (prototype-এর db অবজেক্ট, ধারা দেখুন)।
        $matchCancerOptions = Cache::remember('match_cancer_options', 3600, function () {
            return CancerType::query()
                ->whereIn('slug', self::MATCH_CANCER_SLUGS)
                ->orderBy('sort_order')
                ->get()
                ->sortBy(fn (CancerType $cancerType) => array_search($cancerType->slug, self::MATCH_CANCER_SLUGS, true))
                ->values();
        });

        return view('pages.doctors.show', [
            'doctor' => $doctor,
            'ratingCriteria' => $ratingCriteria,
            'matchCancerOptions' => $matchCancerOptions,
        ]);
    }

    public function match(Request $request, Doctor $doctor, \App\Services\DoctorMatchService $matchService): JsonResponse
    {
        abort_unless($doctor->status === DoctorStatus::Published, 404);

        $result = $matchService->match(
            $doctor,
            $request->query('cancer'),
            $request->query('stage'),
            $request->query('treatment')
        );

        return response()->json($result);
    }
}
