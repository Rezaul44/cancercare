<?php

namespace App\Jobs;

use App\Services\RatingAggregationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RecalculateRatingSummaries implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(RatingAggregationService $service): int
    {
        $count = $service->recalculateAll();

        Log::info("RecalculateRatingSummaries job completed. Recalculated for {$count} doctors.");

        return $count;
    }
}
