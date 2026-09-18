<?php

namespace App\Filament\Resources\DoctorResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CancerTypesRelationManager extends RelationManager
{
    protected static string $relationship = 'cancerTypes';

    protected static ?string $title = 'ক্যান্সার বিশেষত্ব (Cancer Specialties)';

    protected static ?string $modelLabel = 'ক্যান্সারের ধরন';

    protected static ?string $pluralModelLabel = 'ক্যান্সারের ধরনসমূহ';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name_bn')
                    ->label('ক্যান্সারের নাম')
                    ->disabled(),

                Forms\Components\Toggle::make('is_primary')
                    ->label('প্রধান বিশেষত্ব (Primary Specialty)')
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name_bn')
            ->columns([
                Tables\Columns\TextColumn::make('name_bn')
                    ->label('ক্যান্সারের নাম')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('name_en')
                    ->label('ইংরেজি নাম'),

                Tables\Columns\IconColumn::make('is_primary')
                    ->label('প্রধান বিশেষত্ব')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\Toggle::make('is_primary')
                            ->label('প্রধান বিশেষত্ব (Primary Specialty)')
                            ->default(false),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form([
                        Forms\Components\Toggle::make('is_primary')
                            ->label('প্রধান বিশেষত্ব (Primary Specialty)'),
                    ]),
                Tables\Actions\DetachAction::make(),
            ]);
    }
}
