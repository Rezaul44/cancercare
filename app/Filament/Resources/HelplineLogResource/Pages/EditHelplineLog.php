<?php

namespace App\Filament\Resources\HelplineLogResource\Pages;

use App\Filament\Resources\HelplineLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHelplineLog extends EditRecord
{
    protected static string $resource = HelplineLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
