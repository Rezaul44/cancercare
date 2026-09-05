<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CostIndirectRateResource\Pages;
use App\Models\CostIndirectRate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CostIndirectRateResource extends Resource
{
    protected static ?string $model = CostIndirectRate::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'হাসপাতাল ও খরচ';

    protected static ?string $navigationLabel = 'আনুষঙ্গিক খরচ হার (Indirect Rates)';

    protected static ?string $modelLabel = 'আনুষঙ্গিক খরচ';

    protected static ?string $pluralModelLabel = 'আনুষঙ্গিক খরচসমূহ';

    protected static ?int $navigationSort = 4;

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
                Forms\Components\TextInput::make('key')
                    ->label('চিহ্নিতকারী (Key)')
                    ->required(),

                Forms\Components\TextInput::make('label_bn')
                    ->label('বাংলা শিরোনাম')
                    ->required(),

                Forms\Components\TextInput::make('base_amount')
                    ->label('ভিত্তি মূল্য (টাকা)')
                    ->numeric()
                    ->default(0)
                    ->prefix('৳'),

                Forms\Components\Select::make('unit')
                    ->label('হিসাবের একক')
                    ->options([
                        'per_trip' => 'প্রতি যাত্রা (Per Trip)',
                        'per_month' => 'প্রতি মাস (Per Month)',
                        'percent_of_direct' => 'চিকিৎসা খরচের শতাংশ (Percent of Direct)',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('percent_value')
                    ->label('শতাংশ মান (যেমন: 0.18)')
                    ->numeric()
                    ->step(0.01)
                    ->nullable(),

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
                Tables\Columns\TextColumn::make('key')
                    ->label('Key')
                    ->sortable(),

                Tables\Columns\TextColumn::make('label_bn')
                    ->label('শিরোনাম')
                    ->sortable(),

                Tables\Columns\TextInputColumn::make('base_amount')
                    ->label('ভিত্তি মূল্য (৳)')
                    ->sortable(),

                Tables\Columns\TextColumn::make('unit')
                    ->label('একক')
                    ->badge(),

                Tables\Columns\TextInputColumn::make('percent_value')
                    ->label('শতাংশ')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCostIndirectRates::route('/'),
        ];
    }
}
