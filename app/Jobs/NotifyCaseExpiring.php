<?php

namespace App\Jobs;

use App\Enums\PatientCaseStatus;
use App\Models\PatientCase;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class NotifyCaseExpiring implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): int
    {
        $now = Carbon::now();
        $warningThreshold = $now->copy()->addDays(5);

        // Find published cases expiring within the next 5 days
        $expiringCases = PatientCase::query()
            ->where('status', PatientCaseStatus::Published)
            ->whereNotNull('expires_at')
            ->where('expires_at', '>', $now)
            ->where('expires_at', '<=', $warningThreshold)
            ->get();

        if ($expiringCases->isEmpty()) {
            Log::info('NotifyCaseExpiring: No patient cases expiring within 5 days.');
            return 0;
        }

        // Get staff users who manage or verify patient cases
        $staffUsers = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['super_admin', 'admin', 'verification_officer', 'support_agent', 'field_agent']);
        })->get();

        if ($staffUsers->isEmpty()) {
            $staffUsers = User::all();
        }

        $count = 0;
        foreach ($expiringCases as $case) {
            $daysLeft = (int) $now->diffInDays($case->expires_at);

            try {
                Notification::make()
                    ->title("কেসের মেয়াদ শেষ হতে চলেছে: {$case->case_code}")
                    ->body("রোগী {$case->display_name_bn}-এর কেসের মেয়াদ আর {$daysLeft} দিন পর শেষ হবে। নতুন আপডেট বা নবায়ন প্রয়োজন কিনা তা যাচাই করুন।")
                    ->warning()
                    ->actions([
                        Action::make('view')
                            ->label('কেস দেখুন')
                            ->url("/admin/patient-cases/{$case->id}")
                            ->button(),
                    ])
                    ->sendToDatabase($staffUsers);
            } catch (\Throwable $e) {
                Log::warning("Failed to send database notification for expiring case {$case->case_code}: " . $e->getMessage());
            }

            Log::info("NotifyCaseExpiring: Notified staff for case {$case->case_code} (Expires in {$daysLeft} days).");
            $count++;
        }

        Log::info("NotifyCaseExpiring job completed. Notified for {$count} cases.");

        return $count;
    }
}
