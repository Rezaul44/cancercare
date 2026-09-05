<?php

namespace App\Filament\Resources\HospitalResource\RelationManagers;

use App\Services\HospitalService;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ExperienceSummariesRelationManager extends RelationManager
{
    protected static string $relationship = 'experienceSummaries';

    protected static ?string $title = 'রোগীদের বাস্তব অভিজ্ঞতা জরিপ (Patient Experience)';

    protected static ?string $modelLabel = 'অভিজ্ঞতা স্কোর';

    protected static ?string $pluralModelLabel = 'অভিজ্ঞতা স্কোরসমূহ';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('question.label_bn')
            ->columns([
                Tables\Columns\TextColumn::make('question.label_bn')
                    ->label('প্রশ্ন / সূচক')
                    ->weight('semibold')
                    ->wrap(),

                Tables\Columns\TextColumn::make('yes_count')
                    ->label('হ্যাঁ ভোট')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('total_count')
                    ->label('মোট মতামত')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('percentage')
                    ->label('ইতিবাচক হার')
                    ->state(fn ($record) => $record->percentage.'%')
                    ->badge()
                    ->color(fn ($record): string => $record->percentage >= 75 ? 'success' : ($record->percentage >= 50 ? 'warning' : 'danger')),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('প্রকাশিত (>=৩০)')
                    ->boolean()
                    ->tooltip('কমপক্ষে ৩০টি মতামত না পাওয়া পর্যন্ত সাধারণ জনগণের কাছে প্রকাশ করা হয় না।'),

                Tables\Columns\TextColumn::make('last_calculated_at')
                    ->label('হিসাবের তারিখ')
                    ->since(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('recalculate')
                    ->label('স্কোর পুনর্গণনা করুন')
                    ->icon('heroicon-o-calculator')
                    ->action(function (HospitalService $service) {
                        $hospital = $this->getOwnerRecord();
                        $service->recalculateExperienceSummary($hospital);

                        Notification::make()
                            ->title('অভিজ্ঞতা স্কোর সফলভাবে পুনর্গণনা করা হয়েছে')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([])
            ->paginated(false);
    }
}
