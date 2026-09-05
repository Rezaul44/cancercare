<?php

namespace App\Filament\Resources\CostMultiplierResource\Pages;

use App\Filament\Resources\CostMultiplierResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCostMultipliers extends ListRecords
{
    protected static string $resource = CostMultiplierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
