<?php

namespace App\Filament\Resources\HospitalResource\Pages;

use App\Filament\Resources\HospitalResource;
use App\Services\HospitalService;
use Filament\Resources\Pages\CreateRecord;

class CreateHospital extends CreateRecord
{
    protected static string $resource = HospitalResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }

    protected function afterCreate(): void
    {
        app(HospitalService::class)->initializeCapabilities($this->getRecord());
    }
}
