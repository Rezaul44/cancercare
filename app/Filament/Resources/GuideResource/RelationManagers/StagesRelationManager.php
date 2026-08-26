<?php

namespace App\Filament\Resources\GuideResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class StagesRelationManager extends RelationManager
{
    protected static string $relationship = 'stages';

    protected static ?string $title = 'ক্যান্সার স্টেজ ও চিকিৎসা রূপরেখা';

    protected static ?string $modelLabel = 'স্টেজ';

    protected static ?string $pluralModelLabel = 'স্টেজসমূহ';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('stage')
                    ->label('স্টেজ কোড/নাম (যেমন: স্টেজ ১, স্টেজ ২)')
                    ->required()
                    ->maxLength(20),

                Forms\Components\TextInput::make('title_bn')
                    ->label('স্টেজের সংক্ষিপ্ত শিরোনাম (যেমন: টিউমার ছোট, ছড়ায়নি)')
                    ->required()
                    ->maxLength(160),

                Forms\Components\Textarea::make('description_bn')
                    ->label('স্টেজের বিস্তারিত বিবরণ (বাংলা)')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('typical_treatment_bn')
                    ->label('সাধারণ চিকিৎসা পদ্ধতি (যেমন: অপারেশন + রেডিওথেরাপি)')
                    ->required()
                    ->rows(2)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('duration_bn')
                    ->label('আনুমানিক চিকিৎসার সময়কাল (যেমন: ৪–৬ মাস)')
                    ->required()
                    ->maxLength(100),

                Forms\Components\TextInput::make('cost_min')
                    ->label('আনুমানিক ন্যূনতম খরচ (টাকা)')
                    ->numeric()
                    ->prefix('৳'),

                Forms\Components\TextInput::make('cost_max')
                    ->label('আনুমানিক সর্বোচ্চ খরচ (টাকা)')
                    ->numeric()
                    ->prefix('৳'),

                Forms\Components\Select::make('severity_color')
                    ->label('কালার থিম')
                    ->options([
                        'teal' => 'সবুজ-নীল (Teal - মৃদু)',
                        'yellow' => 'হলুদ (Yellow - মাঝারি)',
                        'amber' => 'কমলা (Amber - গুরুতর)',
                        'red' => 'লাল (Red - অতি গুরুতর)',
                    ])
                    ->default('teal'),

                Forms\Components\TextInput::make('sort_order')
                    ->label('ক্রমিক নম্বর')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('stage')
            ->columns([
                Tables\Columns\TextColumn::make('stage')
                    ->label('স্টেজ')
                    ->weight('bold')
                    ->sortable(),

                Tables\Columns\TextColumn::make('title_bn')
                    ->label('শিরোনাম')
                    ->searchable(),

                Tables\Columns\TextColumn::make('typical_treatment_bn')
                    ->label('চিকিৎসা')
                    ->limit(40),

                Tables\Columns\TextColumn::make('duration_bn')
                    ->label('সময়কাল'),

                Tables\Columns\TextColumn::make('cost_min')
                    ->label('ন্যূনতম খরচ')
                    ->money('BDT', divideBy: 1),

                Tables\Columns\TextColumn::make('cost_max')
                    ->label('সর্বোচ্চ খরচ')
                    ->money('BDT', divideBy: 1),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('ক্রম')
                    ->sortable(),
            ])
            ->defaultSort('sort_order', 'asc')
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('নতুন স্টেজ যোগ করুন'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('সম্পাদনা'),
                Tables\Actions\DeleteAction::make()->label('মুছুন'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('নির্বাচিতগুলো মুছুন'),
                ]),
            ]);
    }
}
