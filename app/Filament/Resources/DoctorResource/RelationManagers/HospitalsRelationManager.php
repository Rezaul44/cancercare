<?php

namespace App\Filament\Resources\DoctorResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class HospitalsRelationManager extends RelationManager
{
    protected static string $relationship = 'hospitals';

    protected static ?string $title = 'সংযুক্ত হাসপাতালসমূহ (Associated Hospitals)';

    protected static ?string $modelLabel = 'হাসপাতাল';

    protected static ?string $pluralModelLabel = 'হাসপাতালসমূহ';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name_bn')
                    ->label('হাসপাতালের নাম')
                    ->disabled(),

                Forms\Components\TextInput::make('schedule_note_bn')
                    ->label('হাসপাতালে উপস্থিতির সময়সূচি / শিডিউল নোট')
                    ->maxLength(120)
                    ->placeholder('যেমন: প্রতি শনি ও সোমবার সকাল ৯টা - দুপুর ২টা'),

                Forms\Components\TextInput::make('sort_order')
                    ->label('ক্রম')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name_bn')
            ->columns([
                Tables\Columns\TextColumn::make('name_bn')
                    ->label('হাসপাতাল')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('district.name_bn')
                    ->label('জেলা'),

                Tables\Columns\TextColumn::make('schedule_note_bn')
                    ->label('সময়সূচি নোট'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\TextInput::make('schedule_note_bn')
                            ->label('উপস্থিতির সময়সূচি নোট (বাংলা)')
                            ->placeholder('যেমন: রবি-বৃহস্পতিবার বহির্বিভাগে'),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('ক্রম')
                            ->numeric()
                            ->default(0),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form([
                        Forms\Components\TextInput::make('schedule_note_bn')
                            ->label('উপস্থিতির সময়সূচি নোট (বাংলা)'),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('ক্রম')
                            ->numeric(),
                    ]),
                Tables\Actions\DetachAction::make(),
            ]);
    }
}
