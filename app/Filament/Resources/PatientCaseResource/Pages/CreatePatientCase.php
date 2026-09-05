<?php

namespace App\Filament\Resources\PatientCaseResource\Pages;

use App\Enums\PatientCaseStatus;
use App\Filament\Resources\PatientCaseResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreatePatientCase extends CreateRecord
{
    protected static string $resource = PatientCaseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        if (empty($data['status'])) {
            $data['status'] = PatientCaseStatus::Draft->value;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
