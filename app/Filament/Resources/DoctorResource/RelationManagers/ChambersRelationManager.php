<?php

namespace App\Filament\Resources\DoctorResource\RelationManagers;

use App\Enums\ChamberType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ChambersRelationManager extends RelationManager
{
    protected static string $relationship = 'chambers';

    protected static ?string $title = 'চেম্বার ও অ্যাপয়েন্টমেন্ট শিডিউল (Chambers)';

    protected static ?string $modelLabel = 'চেম্বার';

    protected static ?string $pluralModelLabel = 'চেম্বারসমূহ';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name_bn')
                    ->label('চেম্বারের নাম (বাংলা)')
                    ->required()
                    ->maxLength(160)
                    ->placeholder('যেমন: ল্যাবএইড ক্যান্সার হাসপাতাল ও সুপার স্পেশালিটি সেন্টার')
                    ->columnSpan(2),

                Forms\Components\Select::make('hospital_id')
                    ->label('সংশ্লিষ্ট হাসপাতাল (যদি থাকে)')
                    ->relationship('hospital', 'name_bn')
                    ->searchable()
                    ->preload()
                    ->nullable(),

                Forms\Components\Select::make('district_id')
                    ->label('অবস্থানরত জেলা')
                    ->relationship('district', 'name_bn')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\TextInput::make('address_bn')
                    ->label('পূর্ণাঙ্গ ঠিকানা (বাংলা)')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('যেমন: বাড়ি-১, রোড-৪, ধানমন্ডি, ঢাকা')
                    ->columnSpanFull(),

                Forms\Components\Select::make('type')
                    ->label('চেম্বারের ধরন')
                    ->options([
                        'govt' => 'সরকারি (Govt)',
                        'private' => 'বেসরকারি (Private)',
                        'npo' => 'অলাভজনক / জনহিতকর (NPO)',
                    ])
                    ->default('private')
                    ->required(),

                Forms\Components\TextInput::make('fee')
                    ->label('পরামর্শ ফি (টাকায়)')
                    ->numeric()
                    ->prefix('৳')
                    ->required(),

                Forms\Components\TextInput::make('days_bn')
                    ->label('চেম্বারের দিনসমূহ (বাংলা)')
                    ->required()
                    ->maxLength(120)
                    ->placeholder('যেমন: শনি, সোম, বুধ'),

                Forms\Components\TimePicker::make('time_from')
                    ->label('শুরুর সময়')
                    ->required(),

                Forms\Components\TimePicker::make('time_to')
                    ->label('শেষের সময়')
                    ->required(),

                Forms\Components\TextInput::make('avg_wait_minutes')
                    ->label('গড় অপেক্ষার সময় (মিনিট)')
                    ->numeric()
                    ->nullable(),

                Forms\Components\TextInput::make('next_available_note')
                    ->label('পরের খালি সময় নোট')
                    ->maxLength(120)
                    ->placeholder('যেমন: আগামীকাল খালি বা ৩ দিন পর খালি')
                    ->nullable(),

                Forms\Components\Toggle::make('is_active')
                    ->label('চেম্বারটি বর্তমানে সক্রিয়')
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
            ->recordTitleAttribute('name_bn')
            ->columns([
                Tables\Columns\TextColumn::make('name_bn')
                    ->label('চেম্বারের নাম')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('district.name_bn')
                    ->label('জেলা'),

                Tables\Columns\TextColumn::make('fee')
                    ->label('ফি')
                    ->money('BDT', locale: 'bn_BD'),

                Tables\Columns\TextColumn::make('days_bn')
                    ->label('দিনসমূহ'),

                Tables\Columns\TextColumn::make('time_from')
                    ->label('সময়')
                    ->formatStateUsing(fn ($record) => $record->time_from && $record->time_to
                        ? date('g:i A', strtotime($record->time_from)).' – '.date('g:i A', strtotime($record->time_to))
                        : '—'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('সক্রিয়')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('নতুন চেম্বার যোগ'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
