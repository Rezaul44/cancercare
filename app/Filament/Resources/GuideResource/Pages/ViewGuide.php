<?php

namespace App\Filament\Resources\GuideResource\Pages;

use App\Filament\Resources\GuideResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewGuide extends ViewRecord
{
    protected static string $resource = GuideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('সম্পাদনা'),
        ];
    }
}
