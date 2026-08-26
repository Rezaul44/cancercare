<?php

namespace App\Filament\Resources\GuideResource\Pages;

use App\Enums\GuideStatus;
use App\Filament\Resources\GuideResource;
use App\Models\Doctor;
use App\Models\Guide;
use App\Services\GuideService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditGuide extends EditRecord
{
    protected static string $resource = GuideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('medicalApprove')
                ->label('চিকিৎসা তথ্য অনুমোদন')
                ->icon('heroicon-o-check-badge')
                ->color('info')
                ->visible(fn (): bool => (bool) Auth::user()?->can('guides.medical_approve'))
                ->form([
                    Forms\Components\Select::make('doctor_id')
                        ->label('অনুমোদনকারী / পর্যালোচক ডাক্তার')
                        ->options(Doctor::query()->orderBy('name_bn')->pluck('name_bn', 'id'))
                        ->searchable()
                        ->required()
                        ->default(fn (Guide $record) => $record->reviewed_by_doctor_id),
                    Forms\Components\Textarea::make('note')
                        ->label('পর্যালোচনা মন্তব্য (ঐচ্ছিক)')
                        ->rows(3),
                ])
                ->action(function (Guide $record, array $data): void {
                    app(GuideService::class)->medicalApprove($record, (int) $data['doctor_id'], $data['note'] ?? null, Auth::user());

                    Notification::make()
                        ->title('চিকিৎসা তথ্য সফলভাবে অনুমোদিত হয়েছে')
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'reviewed_by_doctor_id', 'reviewed_at']);
                }),

            Actions\Action::make('publish')
                ->label('প্রকাশ করুন')
                ->icon('heroicon-o-globe-alt')
                ->color('success')
                ->visible(fn (Guide $record): bool => Auth::user()?->can('guides.publish') && $record->status !== GuideStatus::Published)
                ->disabled(fn (Guide $record): bool => empty($record->reviewed_at) || empty($record->reviewed_by_doctor_id))
                ->tooltip(fn (Guide $record): ?string => (empty($record->reviewed_at) || empty($record->reviewed_by_doctor_id))
                    ? 'ডাক্তার কর্তৃক পর্যালোচিত না হলে প্রকাশ করা যাবে না'
                    : null
                )
                ->requiresConfirmation()
                ->modalHeading('গাইড প্রকাশ নিশ্চিতকরণ')
                ->modalDescription('আপনি কি নিশ্চিত যে এই গাইডটি ওয়েবসাইটে সর্বসাধারণের জন্য প্রকাশ করতে চান?')
                ->action(function (Guide $record): void {
                    app(GuideService::class)->publish($record, Auth::user());

                    Notification::make()
                        ->title('গাইডটি সফলভাবে প্রকাশিত হয়েছে')
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'published_at', 'last_updated_at']);
                }),

            Actions\Action::make('unpublish')
                ->label('খসড়া করুন')
                ->icon('heroicon-o-archive-box-arrow-down')
                ->color('gray')
                ->visible(fn (Guide $record): bool => Auth::user()?->can('guides.publish') && $record->status === GuideStatus::Published)
                ->requiresConfirmation()
                ->modalHeading('গাইড খসড়া রূপান্তর')
                ->modalDescription('গাইডটি খসড়া করলে তা ওয়েবসাইটে প্রদর্শিত হবে না।')
                ->action(function (Guide $record): void {
                    app(GuideService::class)->unpublish($record, Auth::user());

                    Notification::make()
                        ->title('গাইডটি খসড়া হিসেবে সংরক্ষিত হয়েছে')
                        ->info()
                        ->send();

                    $this->refreshFormData(['status']);
                }),

            Actions\DeleteAction::make()
                ->label('মুছুন'),
        ];
    }
}
