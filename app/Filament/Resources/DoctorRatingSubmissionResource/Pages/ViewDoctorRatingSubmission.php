<?php

namespace App\Filament\Resources\DoctorRatingSubmissionResource\Pages;

use App\Filament\Resources\DoctorRatingSubmissionResource;
use App\Models\DoctorRatingSubmission;
use App\Services\RatingAggregationService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewDoctorRatingSubmission extends ViewRecord
{
    protected static string $resource = DoctorRatingSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        /** @var DoctorRatingSubmission $record */
        $record = $this->getRecord();

        return [
            Action::make('verify')
                ->label('যাচাই সম্পন্ন')
                ->icon('heroicon-o-check-badge')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn (): bool => ! $record->is_verified)
                ->action(function (RatingAggregationService $service) use ($record): void {
                    $service->verifySubmission($record, Auth::user());

                    Notification::make()
                        ->title('রেটিং যাচাই করা হয়েছে')
                        ->success()
                        ->send();

                    $this->redirect(static::getResource()::getUrl('view', ['record' => $record]));
                }),
        ];
    }
}
