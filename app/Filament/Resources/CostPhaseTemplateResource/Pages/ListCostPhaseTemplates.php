<?php

namespace App\Filament\Resources\CostPhaseTemplateResource\Pages;

use App\Filament\Resources\CostPhaseTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCostPhaseTemplates extends ListRecords
{
    protected static string $resource = CostPhaseTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
