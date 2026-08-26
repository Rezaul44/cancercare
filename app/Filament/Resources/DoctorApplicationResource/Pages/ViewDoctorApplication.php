<?php

namespace App\Filament\Resources\DoctorApplicationResource\Pages;

use App\Enums\DoctorApplicationStatus;
use App\Filament\Resources\DoctorApplicationResource;
use App\Models\DoctorApplication;
use App\Services\DoctorApplicationService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewDoctorApplication extends ViewRecord
{
    protected static string $resource = DoctorApplicationResource::class;

    protected function getHeaderActions(): array
    {
        /** @var DoctorApplication $record */
        $record = $this->getRecord();

        return [
            Action::make('verify')
                ->label('যাচাই সম্পন্ন')
                ->icon('heroicon-o-check-badge')
                ->color('warning')
                ->visible(fn (): bool => ! in_array($record->status, [
                    DoctorApplicationStatus::Approved->value,
                    DoctorApplicationStatus::Rejected->value,
                ], true))
                ->form(DoctorApplicationResource::verifyFormSchema())
                ->fillForm([
                    'bmdc_verified' => (bool) ($record->verification_checklist['bmdc_verified'] ?? false),
                    'degree_verified' => (bool) ($record->verification_checklist['degree_verified'] ?? false),
                    'note' => $record->verification_checklist['note'] ?? null,
                ])
                ->action(function (array $data, DoctorApplicationService $service) use ($record): void {
                    $service->markVerified($record, $data, Auth::user());

                    Notification::make()
                        ->title('যাচাই checklist সংরক্ষণ করা হয়েছে')
                        ->success()
                        ->send();

                    $this->redirect(static::getResource()::getUrl('view', ['record' => $record]));
                }),

            Action::make('approve')
                ->label('অনুমোদন')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool => $record->isVerified() && ! in_array($record->status, [
                    DoctorApplicationStatus::Approved->value,
                    DoctorApplicationStatus::Rejected->value,
                ], true))
                ->form(DoctorApplicationResource::approveFormSchema())
                ->fillForm(DoctorApplicationResource::approveFormDefaults($record))
                ->action(function (array $data, DoctorApplicationService $service) use ($record): void {
                    $service->approve($record, $data, Auth::user());

                    Notification::make()
                        ->title('আবেদন অনুমোদিত হয়েছে এবং ডাক্তার প্রোফাইল তৈরি হয়েছে')
                        ->success()
                        ->send();

                    $this->redirect(static::getResource()::getUrl('view', ['record' => $record]));
                }),

            Action::make('reject')
                ->label('বাতিল')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn (): bool => ! in_array($record->status, [
                    DoctorApplicationStatus::Approved->value,
                    DoctorApplicationStatus::Rejected->value,
                ], true))
                ->form(DoctorApplicationResource::rejectFormSchema())
                ->action(function (array $data, DoctorApplicationService $service) use ($record): void {
                    $service->reject($record, $data['reason'], Auth::user());

                    Notification::make()
                        ->title('আবেদন বাতিল করা হয়েছে')
                        ->danger()
                        ->send();

                    $this->redirect(static::getResource()::getUrl('view', ['record' => $record]));
                }),
        ];
    }
}
