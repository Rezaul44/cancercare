<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CostMultiplierResource\Pages;
use App\Models\CostMultiplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CostMultiplierResource extends Resource
{
    protected static ?string $model = CostMultiplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-variable';

    protected static ?string $navigationGroup = 'হাসপাতাল ও খরচ';

    protected static ?string $navigationLabel = 'খরচ গুণক (Multipliers)';

    protected static ?string $modelLabel = 'খরচ গুণক';

    protected static ?string $pluralModelLabel = 'খরচ গুণকসমূহ';

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        return Auth::user()?->can('cost_rates.view') || Auth::user()?->can('cost_rates.manage') || false;
    }

    public static function canView(Model $record): bool
    {
        return Auth::user()?->can('cost_rates.view') || Auth::user()?->can('cost_rates.manage') || false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('cost_rates.manage') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()?->can('cost_rates.manage') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()?->can('cost_rates.manage') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('group')
                    ->label('গ্রুপ')
                    ->options([
                        'stage' => 'স্টেজ (Stage)',
                        'hospital_type' => 'হাসপাতালের ধরন (Hospital Type)',
                        'distance' => 'দূরত্ব (Distance)',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('key')
                    ->label('চিহ্নিতকারী (Key)')
                    ->required(),

                Forms\Components\TextInput::make('multiplier')
                    ->label('গুণক (Multiplier)')
                    ->numeric()
                    ->step(0.01)
                    ->required(),

                Forms\Components\TextInput::make('label_bn')
                    ->label('বাংলা শিরোনাম')
                    ->required(),

                Forms\Components\TextInput::make('sort_order')
                    ->label('ক্রম')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('group')
                    ->label('গ্রুপ')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('key')
                    ->label('Key')
                    ->sortable(),

                Tables\Columns\TextColumn::make('label_bn')
                    ->label('শিরোনাম')
                    ->sortable(),

                Tables\Columns\TextInputColumn::make('multiplier')
                    ->label('গুণক')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('ক্রম')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('group')
                    ->options([
                        'stage' => 'স্টেজ',
                        'hospital_type' => 'হাসপাতালের ধরন',
                        'distance' => 'দূরত্ব',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCostMultipliers::route('/'),
        ];
    }
}
