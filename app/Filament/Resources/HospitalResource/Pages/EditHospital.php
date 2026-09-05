<?php

namespace App\Filament\Resources\HospitalResource\Pages;

use App\Filament\Resources\HospitalResource;
use App\Services\HospitalService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditHospital extends EditRecord
{
    protected static string $resource = HospitalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('syncCapabilities')
                ->label('১১টি সক্ষমতা সিঙ্ক করুন')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->action(function (HospitalService $service) {
                    $service->initializeCapabilities($this->getRecord());
                    Notification::make()
                        ->title('১১টি সক্ষমতা সফলভাবে সিঙ্ক হয়েছে')
                        ->success()
                        ->send();
                }),

            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
