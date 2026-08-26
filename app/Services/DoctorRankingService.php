<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * docs/CLAUDE.md "নীতি ১" (র‍্যাঙ্কিং কেনা যায় না) — এই সার্ভিসে doctor_videos,
 * payments, is_paid_production, হাসপাতালের খ্যাতি, ডাক্তারের নিজের ঘোষিত success rate,
 * বা কোনো priority/featured ফ্ল্যাগ কখনো ব্যবহার করা যাবে না। স্কোর শুধু: বিশেষত্বের মিল,
 * জেলার মিল, যাচাইকৃত (is_published) রেটিং, আর অ্যাপয়েন্টমেন্ট সহজলভ্যতা থেকে আসে।
 */
class DoctorRankingService
{
    private const RATING_SCALE_MAX = 5.0;

    private const MAX_WAIT_MINUTES = 120;

    private const NEUTRAL_AVAILABILITY_RATIO = 0.5;

    /**
     * ভিজিট ফি ফিল্টারের bucket সংজ্ঞা — [min (exclusive), max (inclusive)]।
     * সীমানায় থাকা ফি (৫০০, ১০০০) দুই bucket-এ একসাথে না পড়ার জন্য exclusive/inclusive মিলিয়ে রাখা হলো।
     *
     * @var array<string, array{0: int|null, 1: int|null}>
     */
    public const FEE_BUCKETS = [
        'upto_500' => [null, 500],
        '500_1000' => [500, 1000],
        '1000_plus' => [1000, null],
    ];

    /**
     * স্কোরসহ un-ordered, un-paginated query — নীতি ১ টেস্ট এই মেথডের toSql()/joins
     * পরীক্ষা করে। rotation tie-break বা eager-load এখানে যোগ করা হয় না, rank()-এ হয়।
     *
     * @param  array{
     *     cancer_type_id?: int|null,
     *     district_id?: int|null,
     *     doctor_type_ids?: list<int>,
     *     chamber_types?: list<string>,
     *     fee_buckets?: list<string>,
     *     gender?: list<string>,
     *     facilities?: list<string>,
     * }  $filters
     */
    public function query(array $filters = []): Builder
    {
        $weights = $this->weights();

        $cancerTypeId = $filters['cancer_type_id'] ?? null;
        $districtId = $filters['district_id'] ?? null;
        $doctorTypeIds = $filters['doctor_type_ids'] ?? [];
        $chamberTypes = $filters['chamber_types'] ?? [];
        $feeBuckets = $filters['fee_buckets'] ?? [];
        $genders = $filters['gender'] ?? [];
        $facilities = $filters['facilities'] ?? [];

        $specialty = $this->specialtyScoreExpr($cancerTypeId, $weights);
        $district = $this->districtScoreExpr($districtId, $weights);
        $rating = $this->ratingScoreExpr($weights);
        $availability = $this->availabilityScoreExpr($weights);

        $query = Doctor::query()
            ->published()
            ->leftJoin('doctor_rating_summaries as drs', 'drs.doctor_id', '=', 'doctors.id')
            ->select('doctors.*')
            ->selectRaw("{$specialty['sql']} as specialty_score", $specialty['bindings'])
            ->selectRaw("{$district['sql']} as district_score", $district['bindings'])
            ->selectRaw("{$rating['sql']} as rating_score", $rating['bindings'])
            ->selectRaw("{$availability['sql']} as availability_score", $availability['bindings'])
            ->selectRaw(
                "({$specialty['sql']} + {$district['sql']} + {$rating['sql']} + {$availability['sql']}) as score",
                array_merge($specialty['bindings'], $district['bindings'], $rating['bindings'], $availability['bindings'])
            );

        if (! empty($cancerTypeId)) {
            // হার্ড ফিল্টার: এই cancer type-এ চিকিৎসা দেন না এমন ডাক্তার একেবারেই বাদ।
            $query->whereHas('cancerTypes', fn (Builder $q) => $q->where('cancer_types.id', $cancerTypeId));
        }

        // district_id/chamber_types/fee_buckets একই chamber-এর ওপর একসাথে বসাতে হবে —
        // আলাদা আলাদা whereHas() কল করলে "ভিন্ন ভিন্ন চেম্বার ভিন্ন ভিন্ন শর্ত মেলায়" এই ভুল হতো
        // (যেমন ঢাকার এক চেম্বার + চট্টগ্রামের বেসরকারি আরেক চেম্বার মিলিয়ে "ঢাকা + বেসরকারি" মিথ্যা মিলে যেত)।
        if (! empty($districtId) || ! empty($chamberTypes) || ! empty($feeBuckets)) {
            $query->whereHas('chambers', function (Builder $q) use ($districtId, $chamberTypes, $feeBuckets) {
                $q->where('is_active', true);

                if (! empty($districtId)) {
                    $q->where('district_id', $districtId);
                }

                if (! empty($chamberTypes)) {
                    $q->whereIn('type', $chamberTypes);
                }

                if (! empty($feeBuckets)) {
                    $q->where(function (Builder $feeQuery) use ($feeBuckets) {
                        foreach (array_values($feeBuckets) as $index => $bucket) {
                            if (! array_key_exists($bucket, self::FEE_BUCKETS)) {
                                continue;
                            }

                            [$min, $max] = self::FEE_BUCKETS[$bucket];
                            $method = $index === 0 ? 'where' : 'orWhere';

                            $feeQuery->{$method}(function (Builder $rangeQuery) use ($min, $max) {
                                if ($min !== null) {
                                    $rangeQuery->where('fee', '>', $min);
                                }

                                if ($max !== null) {
                                    $rangeQuery->where('fee', '<=', $max);
                                }
                            });
                        }
                    });
                }
            });
        }

        if (! empty($doctorTypeIds)) {
            // হার্ড ফিল্টার: এই ধরনের অনকোলজিস্ট নন এমন ডাক্তার বাদ। cancerTypes থেকে আলাদা
            // সম্পর্ক (pivot: doctor_doctor_type), তাই আলাদা whereHas।
            $query->whereHas('doctorTypes', fn (Builder $q) => $q->whereIn('doctor_types.id', $doctorTypeIds));
        }

        if (! empty($genders)) {
            $query->whereIn('gender', $genders);
        }

        if (! empty($facilities)) {
            $query->where(function (Builder $q) use ($facilities) {
                $map = ['whatsapp' => 'offers_whatsapp', 'second_opinion' => 'offers_second_opinion'];

                foreach ($facilities as $facility) {
                    if (isset($map[$facility])) {
                        $q->orWhere($map[$facility], true);
                    }
                }
            });
        }

        return $query;
    }

    /**
     * @param  array{
     *     cancer_type_id?: int|null,
     *     district_id?: int|null,
     *     doctor_type_ids?: list<int>,
     *     chamber_types?: list<string>,
     *     fee_buckets?: list<string>,
     *     gender?: list<string>,
     *     facilities?: list<string>,
     * }  $filters
     */
    public function rank(array $filters = [], string $sort = 'relevance', int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->query($filters);

        $this->applySort($query, $sort);

        return $query
            ->orderByRaw('MOD(rotation_seed + ?, 1000)', [now()->weekOfYear])
            ->with(['chambers.district', 'cancerTypes', 'ratingSummary'])
            ->paginate($perPage);
    }

    /**
     * 'relevance' বাদে বাকি সব sort mode-এর জন্য একটা আলাদা, ওয়েট-নিরপেক্ষ কলাম বসানো হয় —
     * যেমন rating sort settings.ranking.weights-এর 'rating' মান শূন্য হয়ে গেলেও ঠিকভাবে কাজ
     * করবে (score/rating_score কলাম ওয়েট দিয়ে গুণ করা, তাই সরাসরি reuse করা হয়নি)।
     */
    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'rating' => $query
                ->selectRaw('(CASE WHEN drs.is_published = 1 THEN COALESCE(drs.overall_score, 0) ELSE -1 END) as rating_sort')
                ->orderByDesc('rating_sort'),
            'fee' => $query
                ->selectRaw(
                    '(SELECT CASE WHEN COUNT(*) = 0 THEN 999999999 ELSE MIN(ch.fee) END '
                    .'FROM chambers ch WHERE ch.doctor_id = doctors.id AND ch.is_active = 1) as fee_sort'
                )
                ->orderBy('fee_sort'),
            'wait' => $query
                ->selectRaw(
                    '(SELECT CASE '
                    .'WHEN COUNT(*) = 0 THEN 999999 '
                    .'WHEN MIN(ch.avg_wait_minutes) IS NULL THEN 100000 '
                    .'ELSE MIN(ch.avg_wait_minutes) END '
                    .'FROM chambers ch WHERE ch.doctor_id = doctors.id AND ch.is_active = 1) as wait_sort'
                )
                ->orderBy('wait_sort'),
            'nearest' => $query
                ->selectRaw(
                    "(SELECT CASE WHEN COUNT(*) = 0 THEN 999 ELSE MIN(CASE d.distance_tier "
                    ."WHEN 'local' THEN 1 WHEN 'near' THEN 2 WHEN 'far' THEN 3 END) END "
                    .'FROM chambers ch INNER JOIN districts d ON d.id = ch.district_id '
                    .'WHERE ch.doctor_id = doctors.id AND ch.is_active = 1) as nearest_sort'
                )
                ->orderBy('nearest_sort'),
            default => $query->orderByDesc('score'),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function weights(): array
    {
        return Setting::group('ranking');
    }

    /**
     * @param  array<string, mixed>  $weights
     * @return array{sql: string, bindings: array<int, mixed>}
     */
    private function specialtyScoreExpr(?int $cancerTypeId, array $weights): array
    {
        if ($cancerTypeId === null) {
            return ['sql' => '0', 'bindings' => []];
        }

        return [
            'sql' => '(SELECT CASE '
                .'WHEN MAX(dct.is_primary) = 1 THEN ? '
                .'WHEN COUNT(*) > 0 THEN ? '
                .'ELSE 0 END '
                .'FROM doctor_cancer_type dct '
                .'WHERE dct.doctor_id = doctors.id AND dct.cancer_type_id = ?)',
            'bindings' => [$weights['primary'] ?? 0, $weights['secondary'] ?? 0, $cancerTypeId],
        ];
    }

    /**
     * @param  array<string, mixed>  $weights
     * @return array{sql: string, bindings: array<int, mixed>}
     */
    private function districtScoreExpr(?int $districtId, array $weights): array
    {
        if ($districtId === null) {
            return ['sql' => '0', 'bindings' => []];
        }

        return [
            'sql' => '(SELECT CASE WHEN COUNT(*) > 0 THEN ? ELSE 0 END '
                .'FROM chambers ch '
                .'WHERE ch.doctor_id = doctors.id AND ch.district_id = ? AND ch.is_active = 1)',
            'bindings' => [$weights['district'] ?? 0, $districtId],
        ];
    }

    /**
     * @param  array<string, mixed>  $weights
     * @return array{sql: string, bindings: array<int, mixed>}
     */
    private function ratingScoreExpr(array $weights): array
    {
        return [
            'sql' => '(CASE WHEN drs.is_published = 1 '
                .'THEN (COALESCE(drs.overall_score, 0) / ?) * ? ELSE 0 END)',
            'bindings' => [self::RATING_SCALE_MAX, $weights['rating'] ?? 0],
        ];
    }

    /**
     * @param  array<string, mixed>  $weights
     * @return array{sql: string, bindings: array<int, mixed>}
     */
    private function availabilityScoreExpr(array $weights): array
    {
        return [
            'sql' => '(SELECT CASE '
                .'WHEN COUNT(*) = 0 THEN 0 '
                .'WHEN MIN(ch.avg_wait_minutes) IS NULL THEN ? '
                .'ELSE GREATEST(0, LEAST(1, 1 - (MIN(ch.avg_wait_minutes) / ?))) '
                .'END FROM chambers ch WHERE ch.doctor_id = doctors.id AND ch.is_active = 1) * ?',
            'bindings' => [self::NEUTRAL_AVAILABILITY_RATIO, (float) self::MAX_WAIT_MINUTES, $weights['availability'] ?? 0],
        ];
    }
}
