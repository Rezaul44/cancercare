<?php

namespace App\Filament\Resources\GuideResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class MythsRelationManager extends RelationManager
{
    protected static string $relationship = 'myths';

    protected static ?string $title = 'ভুল ধারণা ও সত্য (Myths vs Facts)';

    protected static ?string $modelLabel = 'ভুল ধারণা';

    protected static ?string $pluralModelLabel = 'ভুল ধারণা ও সত্যসমূহ';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('myth_bn')
                    ->label('ভুল ধারণা / প্রচলিত মিথ (বাংলা)')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('truth_bn')
                    ->label('ডাক্তারি সত্য / সঠিক তথ্য (বাংলা)')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('sort_order')
                    ->label('ক্রমিক নম্বর')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('myth_bn')
            ->columns([
                Tables\Columns\TextColumn::make('myth_bn')
                    ->label('ভুল ধারণা')
                    ->searchable()
                    ->limit(60)
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('truth_bn')
                    ->label('সঠিক সত্য')
                    ->limit(60),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('ক্রম')
                    ->sortable(),
            ])
            ->defaultSort('sort_order', 'asc')
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('নতুন ভুল ধারণা যোগ করুন'),
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
