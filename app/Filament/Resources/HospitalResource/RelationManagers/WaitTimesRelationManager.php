<?php

namespace App\Filament\Resources\HospitalResource\RelationManagers;

use App\Enums\HospitalWaitTimeSeverity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class WaitTimesRelationManager extends RelationManager
{
    protected static string $relationship = 'waitTimes';

    protected static ?string $title = 'অপেক্ষার সময়সূচি (Wait Times)';

    protected static ?string $modelLabel = 'অপেক্ষার সময়';

    protected static ?string $pluralModelLabel = 'অপেক্ষার সময়সূচি';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('service_key')
                    ->label('সেবার ধরন (Key)')
                    ->options([
                        'first_visit' => 'প্রথম বহির্বিভাগে ডাক্তার দেখানো (First Visit)',
                        'biopsy_report' => 'বায়োপসি ও টেস্ট রিপোর্ট পাওয়া (Biopsy Report)',
                        'chemo_start' => 'কেমোথেরাপি শুরু হওয়া (Chemotherapy Start)',
                        'radiotherapy_start' => 'রেডিওথেরাপির সিরিয়াল পাওয়া (Radiotherapy Start)',
                        'surgery_date' => 'অপারেশনের তারিখ পাওয়া (Surgery Date)',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('label_bn')
                    ->label('শিরোনাম (বাংলা)')
                    ->required()
                    ->maxLength(120)
                    ->placeholder('যেমন: রেডিওথেরাপির সিরিয়াল পাওয়া'),

                Forms\Components\TextInput::make('min_weeks')
                    ->label('সর্বনিম্ন সময় (সপ্তাহ)')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->maxValue(52),

                Forms\Components\TextInput::make('max_weeks')
                    ->label('সর্বোচ্চ সময় (সপ্তাহ)')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->maxValue(52),

                Forms\Components\Select::make('severity')
                    ->label('অপেক্ষার মাত্রা (Severity)')
                    ->options([
                        HospitalWaitTimeSeverity::Short->value => 'স্বল্প (Short - ১-২ সপ্তাহ)',
                        HospitalWaitTimeSeverity::Medium->value => 'মাঝারি (Medium - ২-৪ সপ্তাহ)',
                        HospitalWaitTimeSeverity::Long->value => 'দীর্ঘ (Long - ১ মাসের বেশি)',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('sort_order')
                    ->label('ক্রম (Sort Order)')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('label_bn')
            ->columns([
                Tables\Columns\TextColumn::make('label_bn')
                    ->label('সেবা')
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('weeks_range')
                    ->label('অপেক্ষার সময়')
                    ->state(fn ($record) => $record->min_weeks == $record->max_weeks
                        ? "{$record->min_weeks} সপ্তাহ"
                        : "{$record->min_weeks}–{$record->max_weeks} সপ্তাহ"),

                Tables\Columns\TextColumn::make('severity')
                    ->label('মাত্রা')
                    ->badge()
                    ->color(fn (HospitalWaitTimeSeverity|string $state): string => match ($state instanceof HospitalWaitTimeSeverity ? $state->value : $state) {
                        'short' => 'success',
                        'medium' => 'warning',
                        'long' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state instanceof HospitalWaitTimeSeverity ? $state->value : $state) {
                        'short' => 'স্বল্প',
                        'medium' => 'মাঝারি',
                        'long' => 'দীর্ঘ',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('ক্রম')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('নতুন অপেক্ষার সময় যোগ'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('সম্পাদনা'),
                Tables\Actions\DeleteAction::make()->label('মুছুন'),
            ])
            ->defaultSort('sort_order', 'asc');
    }
}
