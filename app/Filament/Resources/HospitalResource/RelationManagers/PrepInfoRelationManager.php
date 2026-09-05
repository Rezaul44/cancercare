<?php

namespace App\Filament\Resources\HospitalResource\RelationManagers;

use App\Enums\HospitalFlagType;
use App\Enums\HospitalPrepKey;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PrepInfoRelationManager extends RelationManager
{
    protected static string $relationship = 'prepInfos';

    protected static ?string $title = 'আগে থেকে প্রস্তুতি ও সতর্কতা (Preparation Info)';

    protected static ?string $modelLabel = 'প্রস্তুতি তথ্য';

    protected static ?string $pluralModelLabel = 'প্রস্তুতি ও সতর্কতাসমূহ';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('key')
                    ->label('বিষয় (Key)')
                    ->options([
                        HospitalPrepKey::BloodBank->value => 'ব্লাড ব্যাংক ও ডোনার প্রস্তুতি (Blood Bank)',
                        HospitalPrepKey::MedicineSupply->value => 'ওষুধের সহজলভ্যতা (Medicine Supply)',
                        HospitalPrepKey::AttendantPolicy->value => 'রোগীর সঙ্গে থাকার নিয়ম (Attendant Policy)',
                        HospitalPrepKey::RecordsReturn->value => 'রিপোর্ট ও ফাইল সংরক্ষণ (Records Return)',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('title_bn')
                    ->label('শিরোনাম (বাংলা)')
                    ->required()
                    ->maxLength(120)
                    ->placeholder('যেমন: ব্লাড ব্যাংক ও ডোনার প্রস্তুতি'),

                Forms\Components\TextInput::make('flag_text_bn')
                    ->label('সতর্কতা ব্যাজ টেক্সট (বাংলা)')
                    ->maxLength(60)
                    ->placeholder('যেমন: ডোনার সাথে থাকা জরুরি'),

                Forms\Components\Select::make('flag_type')
                    ->label('ব্যাজের ধরন (Flag Type)')
                    ->options([
                        HospitalFlagType::Positive->value => 'ইতিবাচক / সুবিধাজনক (সবুজ)',
                        HospitalFlagType::Warning->value => 'সতর্কতা / প্রস্তুতি দরকার (হলুদ/অ্যাম্বার)',
                        HospitalFlagType::Negative->value => 'সীমাবদ্ধতা / অনুপস্থিত (লাল)',
                    ])
                    ->default(HospitalFlagType::Warning->value)
                    ->required(),

                Forms\Components\Textarea::make('description_bn')
                    ->label('বিস্তারিত বিবরণ (বাংলা)')
                    ->required()
                    ->rows(3)
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
            ->recordTitleAttribute('title_bn')
            ->columns([
                Tables\Columns\TextColumn::make('title_bn')
                    ->label('বিষয়')
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('flag_text_bn')
                    ->label('সতর্কতা ব্যাজ')
                    ->badge()
                    ->color(fn ($record): string => match ($record->flag_type instanceof HospitalFlagType ? $record->flag_type->value : $record->flag_type) {
                        'positive' => 'success',
                        'warning' => 'warning',
                        'negative' => 'danger',
                        default => 'gray',
                    })
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('description_bn')
                    ->label('বিবরণ')
                    ->limit(50)
                    ->wrap(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('ক্রম')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('নতুন প্রস্তুতি তথ্য যোগ'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('সম্পাদনা'),
                Tables\Actions\DeleteAction::make()->label('মুছুন'),
            ])
            ->defaultSort('sort_order', 'asc');
    }
}
