<?php

namespace App\Filament\Resources\CostRateResource\Pages;

use App\Filament\Resources\CostRateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCostRates extends ListRecords
{
    protected static string $resource = CostRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
