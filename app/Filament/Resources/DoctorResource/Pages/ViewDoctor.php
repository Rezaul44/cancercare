<?php

namespace App\Filament\Resources\DoctorResource\Pages;

use App\Filament\Resources\DoctorResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDoctor extends ViewRecord
{
    protected static string $resource = DoctorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view_public')
                ->label('পাবলিক প্রোফাইল দেখুন')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn () => route('doctors.show', $this->getRecord()->slug))
                ->openUrlInNewTab(),

            Actions\EditAction::make(),
        ];
    }
}
