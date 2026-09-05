<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CostPhaseTemplateResource\Pages;
use App\Models\CostPhaseTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CostPhaseTemplateResource extends Resource
{
    protected static ?string $model = CostPhaseTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    protected static ?string $navigationGroup = 'হাসপাতাল ও খরচ';

    protected static ?string $navigationLabel = 'ধাপভিত্তিক খরচ টেমপ্লেট';

    protected static ?string $modelLabel = 'ধাপভিত্তিক টেমপ্লেট';

    protected static ?string $pluralModelLabel = 'ধাপভিত্তিক খরচ টেমপ্লেটসমূহ';

    protected static ?int $navigationSort = 5;

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
                Forms\Components\Select::make('cancer_type_id')
                    ->label('ক্যান্সারের ধরন (সার্বজনীন হলে খালি রাখুন)')
                    ->relationship('cancerType', 'name_bn')
                    ->nullable()
                    ->searchable(),

                Forms\Components\TextInput::make('service_key')
                    ->label('সেবা কী (যেমন: diagnosis, surgery, chemo, radiation, targeted, followup)')
                    ->required(),

                Forms\Components\TextInput::make('phase_title_bn')
                    ->label('ধাপের শিরোনাম')
                    ->required(),

                Forms\Components\TextInput::make('when_bn')
                    ->label('সময়কাল বিবরণ')
                    ->required(),

                Forms\Components\KeyValue::make('breakdown')
                    ->label('উপ-খরচের বিভাজন ও আনুপাতিক হার (যেমন: বায়োপসি ও প্যাথলজি => 0.45)')
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
                Tables\Columns\TextColumn::make('service_key')
                    ->label('সেবা Key')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('phase_title_bn')
                    ->label('ধাপের শিরোনাম')
                    ->sortable(),

                Tables\Columns\TextColumn::make('when_bn')
                    ->label('সময়কাল')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('ক্রম')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCostPhaseTemplates::route('/'),
        ];
    }
}
