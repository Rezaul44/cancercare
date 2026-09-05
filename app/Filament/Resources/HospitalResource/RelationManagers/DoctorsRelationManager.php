<?php

namespace App\Filament\Resources\HospitalResource\RelationManagers;

use App\Models\Doctor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DoctorsRelationManager extends RelationManager
{
    protected static string $relationship = 'doctors';

    protected static ?string $title = 'এখানে যেসব ডাক্তার বসেন (Doctors)';

    protected static ?string $modelLabel = 'ডাক্তার';

    protected static ?string $pluralModelLabel = 'ডাক্তারগণ';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('schedule_note_bn')
                    ->label('চেম্বার বা ডিউটি সময়সূচি নোট (বাংলা)')
                    ->placeholder('যেমন: রবি, মঙ্গল ও বৃহস্পতিবার সকাল ৯টা - দুপুর ১টা (ইউনিট-২)')
                    ->maxLength(200)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('sort_order')
                    ->label('ক্রম (Sort Order)')
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
                    ->label('ডাক্তারের নাম')
                    ->weight('bold')
                    ->description(fn (Doctor $record) => $record->current_position_bn),

                Tables\Columns\TextColumn::make('degrees_line_bn')
                    ->label('ডিগ্রি')
                    ->limit(40),

                Tables\Columns\TextColumn::make('pivot.schedule_note_bn')
                    ->label('সময়সূচি নোট')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('pivot.sort_order')
                    ->label('ক্রম'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('ডাক্তার যুক্ত করুন')
                    ->preloadRecordSelect()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\TextInput::make('schedule_note_bn')
                            ->label('সময়সূচি নোট (বাংলা)')
                            ->placeholder('যেমন: সোম ও বুধবার সকাল ১০টা'),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('ক্রম')
                            ->numeric()
                            ->default(0),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('সময়সূচি সম্পাদন'),
                Tables\Actions\DetachAction::make()->label('অপসারণ'),
            ]);
    }
}
