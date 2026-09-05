<?php

namespace App\Filament\Resources\HelplineLogResource\Pages;

use App\Filament\Resources\HelplineLogResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateHelplineLog extends CreateRecord
{
    protected static string $resource = HelplineLogResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['handled_by'] = Auth::id();

        return $data;
    }
}
