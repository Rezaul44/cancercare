<?php

namespace App\Filament\Resources\PatientCaseResource\Pages;

use App\Enums\PatientCaseStatus;
use App\Filament\Resources\PatientCaseResource;
use App\Models\PatientCase;
use App\Services\VerificationService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewPatientCase extends ViewRecord
{
    protected static string $resource = PatientCaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->label('সম্পাদনা করুন'),

            // Extend Duration Action ("মেয়াদ বাড়াও")
            Actions\Action::make('extendDuration')
                ->label('মেয়াদ বাড়াও')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->modalHeading('রোগী কেসের মেয়াদ ৩০ দিন বৃদ্ধি')
                ->modalDescription('নতুন চিকিৎসার অগ্রগতি বা তথ্যের আপডেট যোগ করে কেসের মেয়াদ ৩০ দিন বাড়ানো হবে।')
                ->visible(fn (PatientCase $record): bool =>
                    $record->status === PatientCaseStatus::Published || $record->status === PatientCaseStatus::Expired
                )
                ->form([
                    \Filament\Forms\Components\Textarea::make('note_bn')
                        ->label('নতুন চিকিৎসার আপডেট / নোট (বাংলায়)')
                        ->placeholder('যেমন: রোগীর ৩য় সাইকেল কেমোথেরাপি সম্পন্ন হয়েছে এবং পরবর্তী চিকিৎসার জন্য সহায়তা প্রয়োজন...')
                        ->required()
                        ->rows(3),

                    \Filament\Forms\Components\Toggle::make('is_public')
                        ->label('আপডেটটি পাবলিক প্রোফাইলে প্রদর্শন করুন')
                        ->default(true),
                ])
                ->action(function (PatientCase $record, array $data): void {
                    $record->updates()->create([
                        'update_date' => \Illuminate\Support\Carbon::now(),
                        'note_bn' => $data['note_bn'],
                        'is_public' => $data['is_public'] ?? true,
                        'created_by' => Auth::id(),
                    ]);

                    $currentExpiry = $record->expires_at;
                    if ($currentExpiry && $currentExpiry->isFuture()) {
                        $record->expires_at = $currentExpiry->copy()->addDays(30);
                    } else {
                        $record->expires_at = \Illuminate\Support\Carbon::now()->addDays(30);
                    }

                    if ($record->status === PatientCaseStatus::Expired) {
                        $record->status = PatientCaseStatus::Published;
                    }

                    $record->save();

                    Notification::make()
                        ->title('কেসের মেয়াদ ৩০ দিন বাড়ানো হয়েছে')
                        ->body("কেস: {$record->case_code}। নতুন মেয়াদ: " . $record->expires_at->translatedFormat('d F Y'))
                        ->success()
                        ->send();
                }),

            Actions\Action::make('publish')
                ->label('প্রকাশ করুন')
                ->icon('heroicon-o-globe-alt')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('রোগী কেস প্রকাশনা অনুমোদন')
                ->modalDescription(function (PatientCase $record, VerificationService $service): string {
                    $missing = $service->getMissingRequirements($record);
                    if (! empty($missing)) {
                        return '⚠️ সতর্কতা: কেসটি এখনো প্রকাশের জন্য প্রস্তুত নয়। অপূর্ণ শর্তসমূহ: ' . implode(', ', $missing);
                    }

                    return 'আপনি কি নিশ্চিত যে এই কেসটি ওয়েবসাইটে প্রকাশের অনুমোদন দিতে চান? এটি ৩০ দিনের জন্য সক্রিয় থাকবে।';
                })
                ->visible(function (PatientCase $record): bool {
                    $user = Auth::user();

                    return $user && $user->can('cases.publish') && $record->status !== PatientCaseStatus::Published;
                })
                ->action(function (PatientCase $record, VerificationService $service): void {
                    try {
                        $service->publishCase($record, Auth::user());

                        Notification::make()
                            ->title('রোগীর কেস সফলভাবে প্রকাশিত হয়েছে')
                            ->body("কেস কোড: {$record->case_code}। মেয়াদ: ৩০ দিন।")
                            ->success()
                            ->send();
                    } catch (\DomainException $e) {
                        Notification::make()
                            ->title('কেস প্রকাশ করা যায়নি')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
