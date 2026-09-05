<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\Guide;
use App\Models\GuideTerm;
use App\Models\Hospital;
use App\Models\PatientCase;
use App\Models\SearchLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

/**
 * এক কোয়েরিতে ৫ ধরনের কন্টেন্ট খুঁজে বের করে, প্রতিটির নিজস্ব ওজন দিয়ে ব়্যাংক করে।
 * MySQL FULLTEXT + ngram parser ব্যবহার করে (বাংলা টেক্সটে ডিফল্ট parser কাজ করে না)।
 */
class SearchService
{
    private const MIN_QUERY_LENGTH = 2;

    private const PER_GROUP_LIMIT = 20;

    private const SUGGEST_PER_TYPE_LIMIT = 3;

    /**
     * @var array<string, float>
     */
    private const TYPE_WEIGHTS = [
        'guide_term' => 1.0,
        'guide' => 0.9,
        'doctor' => 0.8,
        'hospital' => 0.7,
        'patient_case' => 0.5,
    ];

    /**
     * @return array{
     *     all: list<array<string, mixed>>,
     *     doctors: list<array<string, mixed>>,
     *     hospitals: list<array<string, mixed>>,
     *     guides: list<array<string, mixed>>,
     *     patient_cases: list<array<string, mixed>>,
     * }
     */
    public function search(string $query): array
    {
        $results = $this->matchAll($query);

        $grouped = [
            'doctors' => $results->where('type', 'doctor')->values()->all(),
            'hospitals' => $results->where('type', 'hospital')->values()->all(),
            'guides' => $results->whereIn('type', ['guide_term', 'guide'])
                ->sortByDesc('relevance')->values()->all(),
            'patient_cases' => $results->where('type', 'patient_case')->values()->all(),
        ];

        $all = $results->sortByDesc('weighted')->take(self::PER_GROUP_LIMIT)->values()->all();

        $totalCount = $results->count();
        $this->logIfZeroResults($query, $totalCount);

        return array_merge(['all' => $all], $grouped);
    }

    /**
     * @return array<string, list<array<string, mixed>>> হোমপেজ ড্রপডাউনের জন্য, ৪টি UI গ্রুপে ভাগ করা, সীমিত সংখ্যক
     */
    public function suggest(string $query, int $limit = 8): array
    {
        $results = $this->matchAll($query, self::SUGGEST_PER_TYPE_LIMIT);

        $totalCount = $results->count();
        $this->logIfZeroResults($query, $totalCount);

        $top = $results->sortByDesc('weighted')->take($limit)->values();

        return [
            'doctors' => $top->where('type', 'doctor')->values()->all(),
            'hospitals' => $top->where('type', 'hospital')->values()->all(),
            'guides' => $top->whereIn('type', ['guide_term', 'guide'])->values()->all(),
            'patient_cases' => $top->where('type', 'patient_case')->values()->all(),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function matchAll(string $query, ?int $perTypeLimit = null): Collection
    {
        $query = trim($query);

        if (mb_strlen($query) < self::MIN_QUERY_LENGTH) {
            return collect();
        }

        $limit = $perTypeLimit ?? self::PER_GROUP_LIMIT;

        return collect()
            ->concat($this->searchGuideTerms($query, $limit))
            ->concat($this->searchGuides($query, $limit))
            ->concat($this->searchDoctors($query, $limit))
            ->concat($this->searchHospitals($query, $limit))
            ->concat($this->searchPatientCases($query, $limit));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function searchGuideTerms(string $query, int $limit): array
    {
        return GuideTerm::query()
            ->selectRaw('guide_terms.*, MATCH(code, search_keywords, plain_explanation_bn) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance', [$query])
            ->whereRaw('MATCH(code, search_keywords, plain_explanation_bn) AGAINST(? IN NATURAL LANGUAGE MODE)', [$query])
            ->whereHas('guide', fn ($q) => $q->published())
            ->with('guide.cancerType')
            ->orderByDesc('relevance')
            ->limit($limit)
            ->get()
            ->filter(fn (GuideTerm $term) => $term->guide?->cancerType !== null)
            ->map(fn (GuideTerm $term) => $this->formatResult(
                type: 'guide_term',
                title: $term->hint_bn,
                excerpt: $term->plain_explanation_bn,
                url: route('guides.show', $term->guide->cancerType).'#'.$term->slug,
                relevance: (float) $term->relevance,
            ))
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function searchGuides(string $query, int $limit): array
    {
        return Guide::query()
            ->selectRaw('guides.*, MATCH(title_bn, intro_bn) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance', [$query])
            ->whereRaw('MATCH(title_bn, intro_bn) AGAINST(? IN NATURAL LANGUAGE MODE)', [$query])
            ->published()
            ->with('cancerType')
            ->orderByDesc('relevance')
            ->limit($limit)
            ->get()
            ->filter(fn (Guide $guide) => $guide->cancerType !== null)
            ->map(fn (Guide $guide) => $this->formatResult(
                type: 'guide',
                title: $guide->title_bn,
                excerpt: $guide->intro_bn,
                url: route('guides.show', $guide->cancerType),
                relevance: (float) $guide->relevance,
            ))
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function searchDoctors(string $query, int $limit): array
    {
        return Doctor::query()
            ->selectRaw('doctors.*, MATCH(name_bn, degrees_line_bn, current_position_bn) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance', [$query])
            ->whereRaw('MATCH(name_bn, degrees_line_bn, current_position_bn) AGAINST(? IN NATURAL LANGUAGE MODE)', [$query])
            ->published()
            ->orderByDesc('relevance')
            ->limit($limit)
            ->get()
            ->map(fn (Doctor $doctor) => $this->formatResult(
                type: 'doctor',
                title: $doctor->name_bn,
                excerpt: $doctor->current_position_bn,
                url: route('doctors.show', $doctor),
                relevance: (float) $doctor->relevance,
            ))
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function searchHospitals(string $query, int $limit): array
    {
        return Hospital::query()
            ->selectRaw('hospitals.*, MATCH(name_bn, description_bn) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance', [$query])
            ->whereRaw('MATCH(name_bn, description_bn) AGAINST(? IN NATURAL LANGUAGE MODE)', [$query])
            ->published()
            ->orderByDesc('relevance')
            ->limit($limit)
            ->get()
            ->map(fn (Hospital $hospital) => $this->formatResult(
                type: 'hospital',
                title: $hospital->name_bn,
                excerpt: $hospital->description_bn,
                url: route('hospitals.show', $hospital),
                relevance: (float) $hospital->relevance,
            ))
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function searchPatientCases(string $query, int $limit): array
    {
        return PatientCase::query()
            ->selectRaw('patient_cases.*, MATCH(display_name_bn, story_bn) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance', [$query])
            ->whereRaw('MATCH(display_name_bn, story_bn) AGAINST(? IN NATURAL LANGUAGE MODE)', [$query])
            ->published()
            ->orderByDesc('relevance')
            ->limit($limit)
            ->get()
            // real_name কখনো এখানে সিলেক্ট/এক্সপোজ করা হয় না — শুধু display_name_bn ব্যবহার হচ্ছে।
            ->map(fn (PatientCase $case) => $this->formatResult(
                type: 'patient_case',
                title: $case->display_name_bn,
                excerpt: $case->story_bn,
                url: route('patients.show', $case->case_code),
                relevance: (float) $case->relevance,
            ))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function formatResult(string $type, string $title, string $excerpt, string $url, float $relevance): array
    {
        return [
            'type' => $type,
            'title' => $title,
            'excerpt' => \Illuminate\Support\Str::limit(strip_tags($excerpt), 100),
            'url' => $url,
            'relevance' => $relevance,
            'weighted' => $relevance * (self::TYPE_WEIGHTS[$type] ?? 0),
        ];
    }

    private function logIfZeroResults(string $query, int $resultsCount): void
    {
        if ($resultsCount > 0) {
            return;
        }

        SearchLog::create([
            'query' => $query,
            'results_count' => 0,
            'session_hash' => hash('sha256', Session::getId()),
        ]);
    }
}
