<?php

namespace App\Filament\Widgets;

use App\Models\HelplineLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class HelplineStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $todayCount = HelplineLog::whereDate('created_at', Carbon::today())->count();

        $followUpDue = HelplineLog::whereNotNull('follow_up_at')
            ->whereDate('follow_up_at', '<=', Carbon::today())
            ->where('outcome', 'follow_up_needed')
            ->count();

        return [
            Stat::make('আজকের কল', $todayCount)
                ->description('আজ পর্যন্ত মোট কল')
                ->color('info'),
            Stat::make('ফলো-আপ বাকি', $followUpDue)
                ->description('আজ বা তার আগে ফলো-আপ দরকার')
                ->color($followUpDue > 0 ? 'warning' : 'success'),
        ];
    }
}
