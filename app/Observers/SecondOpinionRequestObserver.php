<?php

namespace App\Observers;

use App\Models\SecondOpinionRequest;
use Illuminate\Support\Carbon;

class SecondOpinionRequestObserver
{
    /**
     * Handle the SecondOpinionRequest "creating" event.
     * request_code সিরিয়াল ফরম্যাট: SO-YYYY-0001 (patient_cases-এর case_code জেনারেশনের ধরন অনুসরণ করে)।
     */
    public function creating(SecondOpinionRequest $request): void
    {
        if (! empty($request->request_code)) {
            return;
        }

        $year = Carbon::now()->format('Y');
        $prefix = "SO-{$year}-";

        $latestCode = SecondOpinionRequest::where('request_code', 'like', "{$prefix}%")
            ->orderByDesc('request_code')
            ->value('request_code');

        $nextSequence = 1;
        if ($latestCode && preg_match('/SO-\d{4}-(\d+)/', $latestCode, $matches)) {
            $nextSequence = ((int) $matches[1]) + 1;
        }

        $request->request_code = sprintf('%s%04d', $prefix, $nextSequence);
    }
}
