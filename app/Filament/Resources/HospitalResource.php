<?php

namespace App\Filament\Resources;

use App\Enums\HospitalStatus;
use App\Enums\HospitalType;
use App\Filament\Resources\HospitalResource\Pages;
use App\Filament\Resources\HospitalResource\RelationManagers;
use App\Models\District;
use App\Models\Hospital;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class HospitalResource extends Resource
{
    protected static ?string $model = Hospital::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'হাসপাতাল ও খরচ';

    protected static ?string $navigationLabel = 'হাসপাতাল তালিকা';

    protected static ?string $modelLabel = 'হাসপাতাল';

    protected static ?string $pluralModelLabel = 'হাসপাতালসমূহ';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return Auth::user()?->can('hospitals.view') || Auth::user()?->can('hospitals.manage') || false;
    }

    public static function canView(Model $record): bool
    {
        return Auth::user()?->can('hospitals.view') || Auth::user()?->can('hospitals.manage') || false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('hospitals.manage') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()?->can('hospitals.manage') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()?->can('hospitals.manage') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('মৌলিক তথ্য ও অবস্থান')
                    ->description('হাসপাতালের নাম, ধরন, অবস্থান ও যোগাযোগের বিবরণ')
                    ->schema([
                        Forms\Components\TextInput::make('name_bn')
                            ->label('হাসপাতালের নাম (বাংলা)')
                            ->required()
                            ->maxLength(200)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),

                        Forms\Components\TextInput::make('name_en')
                            ->label('হাসপাতালের নাম (ইংরেজি)')
                            ->required()
                            ->maxLength(200),

                        Forms\Components\TextInput::make('slug')
                            ->label('SEO স্লাগ (URL)')
                            ->required()
                            ->maxLength(200)
                            ->unique(ignoreRecord: true),

                        Forms\Components\Select::make('type')
                            ->label('হাসপাতালের ধরন')
                            ->options([
                                HospitalType::Govt->value => 'সরকারি (Govt)',
                                HospitalType::Private->value => 'বেসরকারি (Private)',
                                HospitalType::Npo->value => 'অলাভজনক / জনহিতকর (NPO)',
                            ])
                            ->required(),

                        Forms\Components\Select::make('district_id')
                            ->label('অবস্থানরত জেলা')
                            ->relationship('district', 'name_bn')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('phone')
                            ->label('হেল্পলাইন / টেলিফোন নম্বর')
                            ->required()
                            ->maxLength(60),

                        Forms\Components\TextInput::make('address_bn')
                            ->label('পূর্ণাঙ্গ ঠিকানা (বাংলা)')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('established_year')
                            ->label('প্রতিষ্ঠার সাল')
                            ->numeric()
                            ->minValue(1900)
                            ->maxValue(date('Y')),

                        Forms\Components\TextInput::make('latitude')
                            ->label('অক্ষাংশ (Latitude)')
                            ->numeric(),

                        Forms\Components\TextInput::make('longitude')
                            ->label('দ্রাঘিমাংশ (Longitude)')
                            ->numeric(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('পরিসর, সক্ষমতা ও সেবামূল্য')
                    ->description('বেড সংখ্যা, অনকোলজিস্ট সংখ্যা, বহিঃবিভাগ টিকিট ও রোগীর পরিসংখ্যান')
                    ->schema([
                        Forms\Components\TextInput::make('bed_count')
                            ->label('মোট বেড সংখ্যা')
                            ->numeric(),

                        Forms\Components\TextInput::make('oncologist_count')
                            ->label('অনকোলজিস্ট সংখ্যা')
                            ->numeric(),

                        Forms\Components\TextInput::make('outdoor_fee')
                            ->label('বহিঃবিভাগ টিকিট ফি')
                            ->numeric()
                            ->prefix('৳'),

                        Forms\Components\TextInput::make('annual_patients')
                            ->label('বাৎসরিক সেবাগ্রহীতা সংখ্যা')
                            ->placeholder('যেমন: ২,৫০,০০০+')
                            ->maxLength(80),

                        Forms\Components\Toggle::make('emergency_24h')
                            ->label('২৪ ঘণ্টা জরুরি সেবা চালু আছে')
                            ->default(false),

                        Forms\Components\FileUpload::make('cover_photo_path')
                            ->label('হাসপাতালের কভার ফটো')
                            ->image()
                            ->directory('hospitals')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description_bn')
                            ->label('হাসপাতালের পরিচিতি ও সারসংক্ষেপ (বাংলা)')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('যাচাই ও প্রকাশনা')
                    ->description('মাঠপর্যায়ে যাচাইয়ের তারিখ ও প্রকাশনা স্ট্যাটাস')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('স্ট্যাটাস')
                            ->options([
                                HospitalStatus::Draft->value => 'খসড়া (Draft)',
                                HospitalStatus::Published->value => 'প্রকাশিত (Published)',
                                HospitalStatus::Suspended->value => 'স্থগিত (Suspended)',
                            ])
                            ->default(HospitalStatus::Draft->value)
                            ->required(),

                        Forms\Components\DatePicker::make('last_verified_at')
                            ->label('সর্বশেষ মাঠ যাচাইয়ের তারিখ')
                            ->helperText('নীতি: প্রতি ৩ মাস অন্তর মাঠকর্মী দ্বারা যাচাই বাধ্যতামূলক।')
                            ->default(now()),

                        Forms\Components\Select::make('verified_by')
                            ->label('যাচাইকারী কর্মকর্তা')
                            ->relationship('verifiedByUser', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_bn')
                    ->label('হাসপাতাল')
                    ->weight('bold')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Hospital $record) => $record->name_en),

                Tables\Columns\TextColumn::make('district.name_bn')
                    ->label('জেলা')
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('ধরন')
                    ->badge()
                    ->color(fn (HospitalType|string $state): string => match ($state instanceof HospitalType ? $state->value : $state) {
                        'govt' => 'info',
                        'private' => 'primary',
                        'npo' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state instanceof HospitalType ? $state->value : $state) {
                        'govt' => 'সরকারি',
                        'private' => 'বেসরকারি',
                        'npo' => 'অলাভজনক',
                        default => $state,
                    }),

                Tables\Columns\IconColumn::make('emergency_24h')
                    ->label('২৪ঘণ্টা জরুরি')
                    ->boolean()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('status')
                    ->label('স্ট্যাটাস')
                    ->badge()
                    ->color(fn (HospitalStatus|string $state): string => match ($state instanceof HospitalStatus ? $state->value : $state) {
                        'draft' => 'gray',
                        'published' => 'success',
                        'suspended' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state instanceof HospitalStatus ? $state->value : $state) {
                        'draft' => 'খসড়া',
                        'published' => 'প্রকাশিত',
                        'suspended' => 'স্থগিত',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('last_verified_at')
                    ->label('সর্বশেষ যাচাই')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('ধরন')
                    ->options([
                        'govt' => 'সরকারি',
                        'private' => 'বেসরকারি',
                        'npo' => 'অলাভজনক',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->label('স্ট্যাটাস')
                    ->options([
                        'draft' => 'খসড়া',
                        'published' => 'প্রকাশিত',
                        'suspended' => 'স্থগিত',
                    ]),

                Tables\Filters\SelectFilter::make('district_id')
                    ->label('জেলা')
                    ->relationship('district', 'name_bn'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CapabilitiesRelationManager::class,
            RelationManagers\WaitTimesRelationManager::class,
            RelationManagers\CostsRelationManager::class,
            RelationManagers\PrepInfoRelationManager::class,
            RelationManagers\PracticalInfoRelationManager::class,
            RelationManagers\VideosRelationManager::class,
            RelationManagers\DoctorsRelationManager::class,
            RelationManagers\ExperienceSummariesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHospitals::route('/'),
            'create' => Pages\CreateHospital::route('/create'),
            'edit' => Pages\EditHospital::route('/{record}/edit'),
            'view' => Pages\ViewHospital::route('/{record}'),
        ];
    }
}
