<?php

namespace App\Filament\Resources\DoctorResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PatientStoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'patientStories';

    protected static ?string $title = 'রোগীদের বাস্তব গল্প (Patient Stories)';

    protected static ?string $modelLabel = 'রোগীর গল্প';

    protected static ?string $pluralModelLabel = 'রোগীদের গল্পসমূহ';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('patient_label_bn')
                    ->label('রোগীর নাম / লেবেল (বাংলা)')
                    ->required()
                    ->maxLength(120)
                    ->placeholder('যেমন: রফিকুল ইসলাম বা রোগী "খ"'),

                Forms\Components\Select::make('cancer_type_id')
                    ->label('ক্যান্সারের ধরন')
                    ->relationship('cancerType', 'name_bn')
                    ->searchable()
                    ->preload()
                    ->nullable(),

                Forms\Components\Select::make('district_id')
                    ->label('রোগীর জেলা')
                    ->relationship('district', 'name_bn')
                    ->searchable()
                    ->preload()
                    ->nullable(),

                Forms\Components\Select::make('stage')
                    ->label('ক্যান্সারের পর্যায় (Stage)')
                    ->options([
                        '1' => 'স্টেজ ১',
                        '2' => 'স্টেজ ২',
                        '3' => 'স্টেজ ৩',
                        '4' => 'স্টেজ ৪',
                        'unknown' => 'অজানা / প্রযোজ্য নয়',
                    ])
                    ->nullable(),

                Forms\Components\TextInput::make('year')
                    ->label('চিকিৎসার সাল')
                    ->numeric()
                    ->default(date('Y'))
                    ->required(),

                Forms\Components\TextInput::make('outcome_duration_bn')
                    ->label('সুস্থতার স্থায়িত্ব / ফলাফল')
                    ->required()
                    ->maxLength(120)
                    ->placeholder('যেমন: ৩ বছর সম্পূর্ণ সুস্থ বা সফল অপারেশন'),

                Forms\Components\TextInput::make('then_bn')
                    ->label('তখন (চিকিৎসার শুরুর অবস্থা)')
                    ->required()
                    ->maxLength(160)
                    ->placeholder('যেমন: ৩য় স্টেজ ও চরম অনিশ্চয়তা'),

                Forms\Components\TextInput::make('now_bn')
                    ->label('এখন (বর্তমান অবস্থা)')
                    ->required()
                    ->maxLength(160)
                    ->placeholder('যেমন: নিয়মিত ফলো-আপে সম্পূর্ণ সুস্থ ও কর্মক্ষম'),

                Forms\Components\Textarea::make('quote_bn')
                    ->label('রোগী বা পরিবারের বক্তব্য / উদ্ধৃতি')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull()
                    ->placeholder('যেমন: "ডাক্তার সাহেবের সময়োপযোগী সিদ্ধান্ত ও নির্ভুল সার্জারিতে আমি নতুন জীবন পেয়েছি।"'),

                Forms\Components\Toggle::make('is_family_told')
                    ->label('পরিবারের বলা গল্প')
                    ->default(false),

                Forms\Components\Toggle::make('is_name_changed')
                    ->label('নাম পরিবর্তিত (গোপনীয়তা রক্ষার্থে)')
                    ->default(true),

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
            ->recordTitleAttribute('patient_label_bn')
            ->columns([
                Tables\Columns\TextColumn::make('patient_label_bn')
                    ->label('রোগীর নাম')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('cancerType.name_bn')
                    ->label('ক্যান্সারের ধরন'),

                Tables\Columns\TextColumn::make('outcome_duration_bn')
                    ->label('ফলাফল')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('year')
                    ->label('সাল'),

                Tables\Columns\IconColumn::make('is_name_changed')
                    ->label('ছদ্মনাম')
                    ->boolean(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('নতুন গল্প যোগ'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
