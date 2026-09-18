<?php

namespace App\Filament\Resources\DoctorResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TimelineRelationManager extends RelationManager
{
    protected static string $relationship = 'timeline';

    protected static ?string $title = 'পেশাগত জীবন ও অভিজ্ঞতা (Timeline)';

    protected static ?string $modelLabel = 'পেশাগত অর্জন / পদবি';

    protected static ?string $pluralModelLabel = 'পেশাগত অভিজ্ঞতাসমূহ';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('year_label')
                    ->label('সময়কাল / সাল')
                    ->required()
                    ->maxLength(60)
                    ->placeholder('যেমন: ২০২০ - বর্তমান বা ২০১৫ - ২০১৮'),

                Forms\Components\TextInput::make('title_bn')
                    ->label('পদবি / দায়িত্ব (বাংলা)')
                    ->required()
                    ->maxLength(160)
                    ->placeholder('যেমন: সহযোগী অধ্যাপক, সার্জিক্যাল অনকোলজি বিভাগ'),

                Forms\Components\TextInput::make('institution_bn')
                    ->label('হাসপাতাল / প্রতিষ্ঠানের নাম (বাংলা)')
                    ->required()
                    ->maxLength(200)
                    ->placeholder('যেমন: জাতীয় ক্যান্সার গবেষণা ইনস্টিটিউট ও হাসপাতাল'),

                Forms\Components\TextInput::make('sort_order')
                    ->label('ক্রম (Sort Order)')
                    ->numeric()
                    ->default(0),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title_bn')
            ->columns([
                Tables\Columns\TextColumn::make('year_label')
                    ->label('সময়কাল')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('title_bn')
                    ->label('পদবি')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('institution_bn')
                    ->label('প্রতিষ্ঠান'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('ক্রম')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('নতুন পদবি যোগ'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
