<?php

namespace App\Filament\Resources\HelplineLogResource\Pages;

use App\Filament\Resources\HelplineLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHelplineLogs extends ListRecords
{
    protected static string $resource = HelplineLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('নতুন কল লগ করুন'),
        ];
    }
}
