<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CostRateResource\Pages;
use App\Models\CancerType;
use App\Models\CostBaseRate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CostRateResource extends Resource
{
    protected static ?string $model = CostBaseRate::class;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    protected static ?string $navigationGroup = 'হাসপাতাল ও খরচ';

    protected static ?string $navigationLabel = 'চিকিৎসা ভিত্তি হার';

    protected static ?string $modelLabel = 'ভিত্তি হার';

    protected static ?string $pluralModelLabel = 'চিকিৎসা ভিত্তি হারসমূহ';

    protected static ?int $navigationSort = 2;

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
                    ->label('ক্যান্সারের ধরন')
                    ->relationship('cancerType', 'name_bn')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\Select::make('service_key')
                    ->label('সেবা')
                    ->options([
                        'diagnosis' => 'নির্ণয় ও প্রাথমিক পরীক্ষা (Diagnosis)',
                        'surgery' => 'অপারেশন (Surgery)',
                        'chemo' => 'কেমোথেরাপি (Chemotherapy)',
                        'radiation' => 'রেডিওথেরাপি (Radiotherapy)',
                        'targeted' => 'টার্গেটেড থেরাপি (Targeted Therapy)',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('govt_amount')
                    ->label('সরকারি হাসপাতালের ভিত্তি মূল্য (টাকা)')
                    ->numeric()
                    ->required()
                    ->prefix('৳'),

                Forms\Components\TextInput::make('default_months')
                    ->label('চিকিৎসার সময়কাল (মাস)')
                    ->numeric()
                    ->default(8)
                    ->required(),

                Forms\Components\Toggle::make('is_applicable')
                    ->label('প্রযোজ্য সেবা')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cancerType.name_bn')
                    ->label('ক্যান্সারের ধরন')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('service_key')
                    ->label('সেবা')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'diagnosis' => 'নির্ণয় ও পরীক্ষা',
                        'surgery' => 'অপারেশন',
                        'chemo' => 'কেমোথেরাপি',
                        'radiation' => 'রেডিওথেরাপি',
                        'targeted' => 'টার্গেটেড থেরাপি',
                        default => $state,
                    })
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextInputColumn::make('govt_amount')
                    ->label('ভিত্তি মূল্য (৳)')
                    ->sortable(),

                Tables\Columns\TextInputColumn::make('default_months')
                    ->label('সময়কাল (মাস)')
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('is_applicable')
                    ->label('প্রযোজ্য')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('সর্বশেষ হালনাগাদ')
                    ->dateTime('d M Y, h:i A')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('cancer_type_id')
                    ->label('ক্যান্সার ফিল্টার')
                    ->relationship('cancerType', 'name_bn'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCostRates::route('/'),
            'create' => Pages\CreateCostRate::route('/create'),
            'edit' => Pages\EditCostRate::route('/{record}/edit'),
        ];
    }
}
