<?php

namespace App\Filament\Resources\DoctorResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PhilosophyPointsRelationManager extends RelationManager
{
    protected static string $relationship = 'philosophyPoints';

    protected static ?string $title = 'চিকিৎসা দর্শন (Philosophy Points)';

    protected static ?string $modelLabel = 'চিকিৎসা দর্শন';

    protected static ?string $pluralModelLabel = 'চিকিৎসা দর্শনসমূহ';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title_bn')
                    ->label('দর্শনের মূল কথা (বাংলা)')
                    ->required()
                    ->maxLength(160)
                    ->placeholder('যেমন: রোগীর সিদ্ধান্ত ও সম্মতিকে সর্বোচ্চ অগ্রাধিকার দেওয়া'),

                Forms\Components\TextInput::make('icon')
                    ->label('আইকন কোড')
                    ->default('heart')
                    ->maxLength(40)
                    ->placeholder('যেমন: heart, shield, bulb, stethoscope, check'),

                Forms\Components\TextInput::make('sort_order')
                    ->label('ক্রম (Sort Order)')
                    ->numeric()
                    ->default(0),

                Forms\Components\Textarea::make('description_bn')
                    ->label('বিস্তারিত বিবরণ (বাংলা)')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull()
                    ->placeholder('যেমন: প্রতিটি চিকিৎসার ভালো ও মন্দ দিক রোগীকে বিস্তারিত বুঝিয়ে সিদ্ধান্ত নেওয়া হয়।'),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title_bn')
            ->columns([
                Tables\Columns\TextColumn::make('title_bn')
                    ->label('মূল কথা')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('icon')
                    ->label('আইকন')
                    ->badge(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('ক্রম')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('নতুন দর্শন যোগ'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
