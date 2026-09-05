<?php

namespace App\Observers;

use App\Enums\PatientCaseStatus;
use App\Models\PatientCase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PatientCaseObserver
{
    /**
     * Handle the PatientCase "creating" event.
     */
    public function creating(PatientCase $patientCase): void
    {
        // 1. Generate sequential case_code in CCB-YYYY-0001 format
        if (empty($patientCase->case_code)) {
            $year = Carbon::now()->format('Y');
            $prefix = "CCB-{$year}-";

            // Find the highest sequence number for this year (including soft-deleted records)
            $latestCode = PatientCase::withTrashed()
                ->where('case_code', 'like', "{$prefix}%")
                ->orderByDesc('case_code')
                ->value('case_code');

            $nextSequence = 1;
            if ($latestCode && preg_match('/CCB-\d{4}-(\d+)/', $latestCode, $matches)) {
                $nextSequence = ((int) $matches[1]) + 1;
            }

            $patientCase->case_code = sprintf('%s%04d', $prefix, $nextSequence);
        }

        // 2. Child protection policy: if age < 18, strictly force show_photo to false
        if (isset($patientCase->age) && (int) $patientCase->age < 18) {
            $patientCase->show_photo = false;
        }

        // 3. If created directly with published status, set published_at and expires_at
        $isPublished = ($patientCase->status instanceof PatientCaseStatus)
            ? $patientCase->status === PatientCaseStatus::Published
            : $patientCase->status === 'published';

        if ($isPublished) {
            if (is_null($patientCase->published_at)) {
                $patientCase->published_at = Carbon::now();
            }
            if (is_null($patientCase->expires_at)) {
                $patientCase->expires_at = Carbon::parse($patientCase->published_at)->addDays(30);
            }
        }
    }

    /**
     * Handle the PatientCase "saving" event.
     */
    public function saving(PatientCase $patientCase): void
    {
        // 1. Child protection policy: if age < 18, strictly force show_photo to false
        if (isset($patientCase->age) && (int) $patientCase->age < 18) {
            $patientCase->show_photo = false;
        }

        // 2. Lifecycle publishing policy: when status transitions to published, set published_at and expires_at
        $isPublished = ($patientCase->status instanceof PatientCaseStatus)
            ? $patientCase->status === PatientCaseStatus::Published
            : $patientCase->status === 'published';

        if ($isPublished) {
            $statusBecamePublished = $patientCase->exists
                && $patientCase->isDirty('status')
                && $patientCase->getOriginal('status') !== null
                && $patientCase->getOriginal('status') !== ($patientCase->status instanceof PatientCaseStatus ? $patientCase->status->value : $patientCase->status);

            if ($statusBecamePublished) {
                $patientCase->published_at = Carbon::now();
                $patientCase->expires_at = Carbon::now()->addDays(30);
            } else {
                if (is_null($patientCase->published_at)) {
                    $patientCase->published_at = Carbon::now();
                }
                if (is_null($patientCase->expires_at)) {
                    $patientCase->expires_at = Carbon::parse($patientCase->published_at)->copy()->addDays(30);
                }
            }
        }
    }
}
