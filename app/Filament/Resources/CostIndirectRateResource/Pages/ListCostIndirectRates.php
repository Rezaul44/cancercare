<?php

namespace App\Filament\Resources\CostIndirectRateResource\Pages;

use App\Filament\Resources\CostIndirectRateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCostIndirectRates extends ListRecords
{
    protected static string $resource = CostIndirectRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
