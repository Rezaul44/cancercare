<?php

namespace App\Filament\Resources\HospitalResource\RelationManagers;

use App\Enums\HospitalPracticalKey;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PracticalInfoRelationManager extends RelationManager
{
    protected static string $relationship = 'practicalInfos';

    protected static ?string $title = 'বাইরের জেলা থেকে এলে করণীয় (Practical Info)';

    protected static ?string $modelLabel = 'ব্যবহারিক তথ্য';

    protected static ?string $pluralModelLabel = 'ব্যবহারিক তথ্যসমূহ';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('key')
                    ->label('বিষয় (Key)')
                    ->options([
                        HospitalPracticalKey::Documents->value => 'প্রয়োজনীয় কাগজপত্র (Documents)',
                        HospitalPracticalKey::Timing->value => 'সময়সূচি ও টিকিট (Timing)',
                        HospitalPracticalKey::Accommodation->value => 'থাকার ব্যবস্থা (Accommodation)',
                        HospitalPracticalKey::Transport->value => 'যাতায়াত ও অবস্থান (Transport)',
                        HospitalPracticalKey::FinancialAid->value => 'আর্থিক সহায়তা ও সমাজসেবা (Financial Aid)',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('title_bn')
                    ->label('শিরোনাম (বাংলা)')
                    ->required()
                    ->maxLength(120)
                    ->placeholder('যেমন: প্রথম দিনে যা যা সাথে আনবেন'),

                Forms\Components\TextInput::make('icon')
                    ->label('আইকন (Tabler Icon Name)')
                    ->placeholder('যেমন: file-text, clock, home, heart-handshake')
                    ->maxLength(60),

                Forms\Components\TextInput::make('sort_order')
                    ->label('ক্রম (Sort Order)')
                    ->numeric()
                    ->default(0),

                Forms\Components\Textarea::make('description_bn')
                    ->label('বিস্তারিত বাস্তব পরামর্শ (বাংলা)')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull()
                    ->placeholder('যেমন: রোগীর এনআইডি, আগের সব প্রেসক্রিপশন ও বায়োপসি স্লাইড সাথে রাখবেন।'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title_bn')
            ->columns([
                Tables\Columns\TextColumn::make('title_bn')
                    ->label('বিষয়')
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('key')
                    ->label('ক্যাটেগরি')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state instanceof HospitalPracticalKey ? $state->value : $state) {
                        'documents' => 'কাগজপত্র',
                        'timing' => 'সময়সূচি',
                        'accommodation' => 'থাকা',
                        'transport' => 'যাতায়াত',
                        'financial_aid' => 'আর্থিক সহায়তা',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('description_bn')
                    ->label('পরামর্শ ও বিবরণ')
                    ->limit(50)
                    ->wrap(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('ক্রম')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('নতুন ব্যবহারিক তথ্য যোগ'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('সম্পাদনা'),
                Tables\Actions\DeleteAction::make()->label('মুছুন'),
            ])
            ->defaultSort('sort_order', 'asc');
    }
}
