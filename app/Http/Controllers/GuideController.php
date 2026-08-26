<?php

namespace App\Http\Controllers;

use App\Enums\GuideStatus;
use App\Models\CancerType;
use App\Models\GuideTerm;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class GuideController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        if ($search === '') {
            $publishedCancerTypes = Cache::remember('guide_index_published_cancer_types', 1800, function () {
                return CancerType::query()
                    ->whereHas('guide', fn ($q) => $q->where('status', GuideStatus::Published))
                    ->with([
                        'guide' => fn ($q) => $q->where('status', GuideStatus::Published),
                        'guide.reviewedByDoctor',
                        'guide.videos',
                        'guide.terms',
                    ])
                    ->orderBy('sort_order')
                    ->get();
            });

            $totalCancerTypesCount = Cache::remember('total_cancer_types_count', 3600, function () {
                return CancerType::count();
            });
            $publishedCount = $publishedCancerTypes->count();
            $unpublishedCount = max(0, $totalCancerTypesCount - $publishedCount);
            $matchingTerms = collect();
        } else {
            $query = CancerType::query()
                ->whereHas('guide', fn ($q) => $q->where('status', GuideStatus::Published))
                ->with([
                    'guide' => fn ($q) => $q->where('status', GuideStatus::Published),
                    'guide.reviewedByDoctor',
                    'guide.videos',
                    'guide.terms',
                ])
                ->orderBy('sort_order');

            $matchingTermGuideIds = GuideTerm::query()
                ->where('code', 'like', "%{$search}%")
                ->orWhere('hint_bn', 'like', "%{$search}%")
                ->orWhere('plain_explanation_bn', 'like', "%{$search}%")
                ->orWhere('search_keywords', 'like', "%{$search}%")
                ->pluck('guide_id');

            $query->where(function ($q) use ($search, $matchingTermGuideIds) {
                $q->where('name_bn', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%")
                    ->orWhere('short_description_bn', 'like', "%{$search}%")
                    ->orWhereIn('id', function ($sub) use ($matchingTermGuideIds) {
                        $sub->select('cancer_type_id')
                            ->from('guides')
                            ->whereIn('id', $matchingTermGuideIds);
                    });
            });

            $publishedCancerTypes = $query->get();
            $totalCancerTypesCount = CancerType::count();
            $publishedCount = CancerType::whereHas('guide', fn ($q) => $q->where('status', GuideStatus::Published))->count();
            $unpublishedCount = max(0, $totalCancerTypesCount - $publishedCount);

            // Matching medical report terms for live search hints
            $matchingTerms = GuideTerm::query()
                ->where('code', 'like', "%{$search}%")
                ->orWhere('hint_bn', 'like', "%{$search}%")
                ->orWhere('plain_explanation_bn', 'like', "%{$search}%")
                ->orWhere('search_keywords', 'like', "%{$search}%")
                ->with('guide.cancerType')
                ->take(5)
                ->get();
        }

        return view('pages.guides.index', [
            'publishedCancerTypes' => $publishedCancerTypes,
            'unpublishedCount' => $unpublishedCount,
            'search' => $search,
            'matchingTerms' => $matchingTerms,
        ]);
    }

    public function show(CancerType $cancerType): View
    {
        $guide = $cancerType->guide;

        abort_unless($guide && $guide->status === GuideStatus::Published, 404);

        $guide->load([
            'reviewedByDoctor',
            'videos.doctor',
            'terms',
            'stages',
            'steps',
            'myths',
            'faqs',
        ]);

        $otherCancerTypes = Cache::remember('guide_other_types_'.$cancerType->id, 1800, function () use ($cancerType) {
            return CancerType::where('id', '!=', $cancerType->id)
                ->whereHas('guide', fn ($q) => $q->where('status', GuideStatus::Published))
                ->orderBy('sort_order')
                ->take(6)
                ->get();
        });

        $doctorCount = $cancerType->doctors()->published()->count();

        return view('pages.guides.show', [
            'cancerType' => $cancerType,
            'guide' => $guide,
            'otherCancerTypes' => $otherCancerTypes,
            'doctorCount' => $doctorCount,
        ]);
    }
}
