<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\DoctorRatingSubmission;
use App\Models\DoctorRatingSummary;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * doctor_rating_submissions (যাচাইকৃত) থেকে doctor_rating_summaries রিক্যালকুলেট করে।
 * docs/CLAUDE.md নীতি ৫ — total_count >= 10 না হলে is_published কখনো true হবে না।
 */
class RatingAggregationService
{
    private const MIN_SUBMISSIONS_TO_PUBLISH = 10;

    private const OVERALL_SCALE_MAX = 5.0;

    public function verifySubmission(DoctorRatingSubmission $submission, User $verifier): void
    {
        $submission->update([
            'is_verified' => true,
            'verified_by' => $verifier->id,
        ]);

        activity()
            ->causedBy($verifier)
            ->performedOn($submission)
            ->withProperties(['doctor_id' => $submission->doctor_id])
            ->log('doctor_rating_submission.verified');
    }

    public function recalculateAll(): int
    {
        $doctorIds = DoctorRatingSubmission::where('is_verified', true)
            ->distinct()
            ->pluck('doctor_id');

        foreach ($doctorIds as $doctorId) {
            $doctor = Doctor::find($doctorId);

            if ($doctor) {
                $this->recalculateForDoctor($doctor);
            }
        }

        return $doctorIds->count();
    }

    public function recalculateForDoctor(Doctor $doctor): void
    {
        $submissions = DoctorRatingSubmission::where('doctor_id', $doctor->id)
            ->where('is_verified', true)
            ->get();

        $totalCount = $submissions->count();
        $criteriaScores = $this->calculateCriteriaScores($submissions);
        $overallScore = $this->calculateOverallScore($criteriaScores);

        DoctorRatingSummary::updateOrCreate(
            ['doctor_id' => $doctor->id],
            [
                'total_count' => $totalCount,
                'criteria_scores' => $criteriaScores,
                'overall_score' => $overallScore,
                'is_published' => $totalCount >= self::MIN_SUBMISSIONS_TO_PUBLISH,
                'last_calculated_at' => Carbon::now(),
            ]
        );
    }

    /**
     * @param  \Illuminate\Support\Collection<int, DoctorRatingSubmission>  $submissions
     * @return array<string, int> প্রতিটি criterion-এর "হ্যাঁ" শতাংশ (0-100), যেসব criterion অন্তত একবার জিজ্ঞেস হয়েছে শুধু সেগুলোই
     */
    private function calculateCriteriaScores($submissions): array
    {
        $tally = []; // key => ['yes' => int, 'total' => int]

        foreach ($submissions as $submission) {
            foreach ((array) $submission->answers as $key => $value) {
                $tally[$key] ??= ['yes' => 0, 'total' => 0];
                $tally[$key]['total']++;

                if (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
                    $tally[$key]['yes']++;
                }
            }
        }

        return collect($tally)
            ->map(fn (array $counts): int => (int) round(($counts['yes'] / $counts['total']) * 100))
            ->all();
    }

    /**
     * @param  array<string, int>  $criteriaScores
     */
    private function calculateOverallScore(array $criteriaScores): ?string
    {
        if (empty($criteriaScores)) {
            return null;
        }

        $averagePercentage = array_sum($criteriaScores) / count($criteriaScores);

        // decimal(2,1) কলামে string দিয়ে সেট করা হচ্ছে — float পাঠালে brick/math deprecation warning দেয়।
        return number_format(($averagePercentage / 100) * self::OVERALL_SCALE_MAX, 1, '.', '');
    }
}
