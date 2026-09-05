<?php

namespace App\Filament\Resources\HospitalResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CostsRelationManager extends RelationManager
{
    protected static string $relationship = 'costs';

    protected static ?string $title = 'চিকিৎসা খরচ কাঠামো (Hospital Costs)';

    protected static ?string $modelLabel = 'খরচ তথ্য';

    protected static ?string $pluralModelLabel = 'চিকিৎসা খরচ কাঠামো';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('service_key')
                    ->label('সেবার ধরন (Key)')
                    ->required()
                    ->maxLength(60)
                    ->placeholder('যেমন: chemo_per_cycle, radiotherapy_full'),

                Forms\Components\TextInput::make('label_bn')
                    ->label('সেবার শিরোনাম (বাংলা)')
                    ->maxLength(120)
                    ->placeholder('যেমন: কেমোথেরাপি (প্রতি সাইকেল সরকারি চার্জ)'),

                Forms\Components\TextInput::make('min_amount')
                    ->label('সর্বনিম্ন খরচ (টাকা)')
                    ->numeric()
                    ->required()
                    ->prefix('৳'),

                Forms\Components\TextInput::make('max_amount')
                    ->label('সর্বোচ্চ খরচ (টাকা)')
                    ->numeric()
                    ->required()
                    ->prefix('৳'),

                Forms\Components\Textarea::make('note_bn')
                    ->label('খরচের শর্ত ও বিশেষ নোট (বাংলা)')
                    ->placeholder('যেমন: ওষুধের খরচ আলাদা, রোগীর বাইরে থেকে কিনতে হতে পারে।')
                    ->rows(2)
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
            ->recordTitleAttribute('label_bn')
            ->columns([
                Tables\Columns\TextColumn::make('label_bn')
                    ->label('সেবা')
                    ->weight('semibold')
                    ->placeholder(fn ($record) => $record->service_key),

                Tables\Columns\TextColumn::make('cost_range')
                    ->label('খরচের ব্যাপ্তি')
                    ->state(fn ($record) => '৳'.number_format($record->min_amount).' – ৳'.number_format($record->max_amount)),

                Tables\Columns\TextColumn::make('note_bn')
                    ->label('নোট')
                    ->limit(40)
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('ক্রম')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('নতুন খরচ তথ্য যোগ'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('সম্পাদনা'),
                Tables\Actions\DeleteAction::make()->label('মুছুন'),
            ])
            ->defaultSort('sort_order', 'asc');
    }
}
