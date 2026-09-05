<?php

namespace App\Filament\Widgets;

use App\Models\HelplineLog;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

/**
 * সাপ্তাহিক প্রতিবেদন: গত ৭ দিনে কোন বিষয়ে সবচেয়ে বেশি কল এসেছে।
 */
class HelplineTopicsWidget extends Widget
{
    protected static string $view = 'filament.widgets.helpline-topics-widget';

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $counts = HelplineLog::query()
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->selectRaw('topic, COUNT(*) as total')
            ->groupBy('topic')
            ->orderByDesc('total')
            ->get();

        $rows = $counts->map(fn ($row) => [
            'label' => $row->topic->labelBn(),
            'total' => $row->total,
        ]);

        return ['rows' => $rows];
    }
}
