<?php

namespace App\Filament\Resources\GuideResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TermsRelationManager extends RelationManager
{
    protected static string $relationship = 'terms';

    protected static ?string $title = 'রিপোর্ট ডিকোডার টার্মস';

    protected static ?string $modelLabel = 'টার্ম';

    protected static ?string $pluralModelLabel = 'রিপোর্ট টার্মসমূহ';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label('মেডিকেল কোড / নাম (যেমন: HER2 Positive)')
                    ->required()
                    ->maxLength(120),

                Forms\Components\TextInput::make('slug')
                    ->label('অ্যাঙ্কর স্লাগ (ঐচ্ছিক, ফাঁকা রাখলে কোড থেকে তৈরি হবে)')
                    ->maxLength(140),

                Forms\Components\TextInput::make('hint_bn')
                    ->label('সহজ ইঙ্গিত (বাংলা, যেমন: রিসেপ্টর প্রোটিন)')
                    ->required()
                    ->maxLength(120),

                Forms\Components\Textarea::make('plain_explanation_bn')
                    ->label('সহজ ব্যাখ্যা (বাংলা)')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('why_matters_bn')
                    ->label('কেন গুরুত্বপূর্ণ / সিদ্ধান্তের প্রভাব (বাংলা)')
                    ->rows(2)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('search_keywords')
                    ->label('সার্চ কিওয়ার্ডস (কমা দিয়ে পৃথক)')
                    ->maxLength(255),

                Forms\Components\TextInput::make('sort_order')
                    ->label('ক্রমিক নম্বর')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('code')
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('কোড / টার্ম')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('hint_bn')
                    ->label('ইঙ্গিত')
                    ->searchable(),

                Tables\Columns\TextColumn::make('plain_explanation_bn')
                    ->label('সহজ ব্যাখ্যা')
                    ->limit(50),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('ক্রম')
                    ->sortable(),
            ])
            ->defaultSort('sort_order', 'asc')
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('নতুন টার্ম যোগ করুন'),
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
