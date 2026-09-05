<?php

namespace App\Services;

use App\Enums\PatientCaseStatus;
use App\Enums\PatientCaseVerificationStatus;
use App\Enums\PatientCaseVerificationStep;
use App\Models\PatientCase;
use App\Models\User;
use Illuminate\Support\Carbon;

class VerificationService
{
    /**
     * All 4 required verification steps in order.
     *
     * @var list<string>
     */
    public const REQUIRED_STEPS = [
        'documents',
        'hospital_confirm',
        'identity',
        'field_meeting',
    ];

    /**
     * Check if a patient case is fully verified and eligible for publication.
     *
     * Requirements:
     * 1. All 4 verification steps must be marked as 'done' (PatientCaseVerificationStatus::Done).
     * 2. Written consent form path (consent_form_path) must be present.
     * 3. At least one recipient account must have name_verified = true.
     */
    public function canPublishCase(PatientCase $case): bool
    {
        return empty($this->getMissingRequirements($case));
    }

    /**
     * Get a list of unmet verification criteria for the patient case.
     *
     * @return array<int, string>
     */
    public function getMissingRequirements(PatientCase $case): array
    {
        $missing = [];

        // 1. Check all 4 verification steps
        $verifications = $case->relationLoaded('verifications')
            ? $case->verifications
            : $case->verifications()->get();

        $completedSteps = $verifications
            ->filter(function ($v) {
                $status = ($v->status instanceof PatientCaseVerificationStatus)
                    ? $v->status
                    : PatientCaseVerificationStatus::tryFrom((string) $v->status);

                return $status === PatientCaseVerificationStatus::Done;
            })
            ->map(function ($v) {
                return ($v->step instanceof PatientCaseVerificationStep)
                    ? $v->step->value
                    : (string) $v->step;
            })
            ->all();

        foreach (self::REQUIRED_STEPS as $stepKey) {
            if (! in_array($stepKey, $completedSteps, true)) {
                $stepEnum = PatientCaseVerificationStep::tryFrom($stepKey);
                $stepLabel = $stepEnum ? $stepEnum->labelBn() : $stepKey;
                $missing[] = "যাচাইকরণ ধাপ অসম্পূর্ণ: {$stepLabel}";
            }
        }

        // 2. Check consent form
        if (empty($case->consent_form_path)) {
            $missing[] = 'লিখিত সম্মতিপত্রের স্ক্যান কপি (Consent Form) আপলোড করা হয়নি';
        }

        // 3. Check verified account
        $accounts = $case->relationLoaded('accounts')
            ? $case->accounts
            : $case->accounts()->get();

        $hasVerifiedAccount = $accounts->contains(function ($account) {
            return (bool) $account->name_verified && (bool) ($account->is_active ?? true);
        });

        if (! $hasVerifiedAccount) {
            $missing[] = 'রোগীর অন্তত একটি নাম-যাচাইকৃত (Name Verified) বিকাশ/নগদ/ব্যাংক অ্যাকাউন্ট প্রয়োজন';
        }

        return $missing;
    }

    /**
     * Calculate 4-step verification progress statistics.
     *
     * @return array{done_count: int, total_count: int, percentage: int, steps: array<string, bool>}
     */
    public function getVerificationProgress(PatientCase $case): array
    {
        $verifications = $case->relationLoaded('verifications')
            ? $case->verifications
            : $case->verifications()->get();

        $stepStatusMap = [];
        $doneCount = 0;

        foreach (self::REQUIRED_STEPS as $stepKey) {
            $verification = $verifications->first(function ($v) use ($stepKey) {
                $key = ($v->step instanceof PatientCaseVerificationStep)
                    ? $v->step->value
                    : (string) $v->step;

                return $key === $stepKey;
            });

            $isDone = false;
            if ($verification) {
                $status = ($verification->status instanceof PatientCaseVerificationStatus)
                    ? $verification->status
                    : PatientCaseVerificationStatus::tryFrom((string) $verification->status);

                $isDone = ($status === PatientCaseVerificationStatus::Done);
            }

            $stepStatusMap[$stepKey] = $isDone;
            if ($isDone) {
                $doneCount++;
            }
        }

        $totalCount = count(self::REQUIRED_STEPS);
        $percentage = (int) round(($doneCount / $totalCount) * 100);

        return [
            'done_count' => $doneCount,
            'total_count' => $totalCount,
            'percentage' => $percentage,
            'steps' => $stepStatusMap,
        ];
    }

    /**
     * Publish a patient case after verifying all strict criteria.
     *
     * @throws \DomainException If verification criteria are not fully met
     */
    public function publishCase(PatientCase $case, ?User $user = null): bool
    {
        $missing = $this->getMissingRequirements($case);

        if (! empty($missing)) {
            $message = 'কেস প্রকাশ করা সম্ভব নয়। অপূর্ণ শর্তসমূহ: ' . implode(', ', $missing);
            throw new \DomainException($message);
        }

        $case->status = PatientCaseStatus::Published;
        $case->verified_by = $user?->id;
        $case->verified_at = Carbon::now();
        $case->published_at = Carbon::now();
        $case->expires_at = Carbon::now()->addDays(30);

        $saved = $case->save();

        return $saved;
    }
}
