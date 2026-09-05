<?php

namespace App\Jobs;

use App\Enums\PatientCaseStatus;
use App\Models\PatientCase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ExpirePatientCases implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): int
    {
        $now = Carbon::now();

        $expiredCases = PatientCase::query()
            ->where('status', PatientCaseStatus::Published)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now)
            ->get();

        $count = 0;
        foreach ($expiredCases as $case) {
            $case->update([
                'status' => PatientCaseStatus::Expired,
            ]);
            $count++;
            Log::info("Patient case expired: {$case->case_code} (ID: {$case->id})");
        }

        Log::info("ExpirePatientCases job completed. Total expired cases: {$count}");

        return $count;
    }
}
