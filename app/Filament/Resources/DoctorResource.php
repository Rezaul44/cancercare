<?php

namespace App\Filament\Resources;

use App\Enums\DoctorStatus;
use App\Filament\Resources\DoctorResource\Pages;
use App\Filament\Resources\DoctorResource\RelationManagers;
use App\Models\Doctor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DoctorResource extends Resource
{
    protected static ?string $model = Doctor::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'ডাক্তার ও কনসালটেশন';

    protected static ?string $navigationLabel = 'ডাক্তার তালিকা';

    protected static ?string $modelLabel = 'ডাক্তার';

    protected static ?string $pluralModelLabel = 'ডাক্তারবৃন্দ';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return Auth::user()?->can('doctors.view') || Auth::user()?->can('doctors.manage') || false;
    }

    public static function canView(Model $record): bool
    {
        return Auth::user()?->can('doctors.view') || Auth::user()?->can('doctors.manage') || false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('doctors.manage') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()?->can('doctors.manage') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()?->can('doctors.manage') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('মৌলিক তথ্য ও পরিচিতি')
                    ->description('ডাক্তারের নাম, BMDC নম্বর, পদবি ও ডিগ্রির বিবরণ')
                    ->schema([
                        Forms\Components\TextInput::make('name_bn')
                            ->label('ডাক্তারের নাম (বাংলা)')
                            ->required()
                            ->maxLength(120)
                            ->placeholder('যেমন: অধ্যাপক ডা. মোঃ রফিকুল ইসলাম')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),

                        Forms\Components\TextInput::make('name_en')
                            ->label('ডাক্তারের নাম (ইংরেজি)')
                            ->required()
                            ->maxLength(120)
                            ->placeholder('যেমন: Prof. Dr. Md. Rafiqul Islam'),

                        Forms\Components\TextInput::make('slug')
                            ->label('SEO স্লাগ (URL)')
                            ->required()
                            ->maxLength(160)
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('bmdc_number')
                            ->label('BMDC রেজিস্ট্রেশন নম্বর')
                            ->required()
                            ->maxLength(60)
                            ->unique(ignoreRecord: true)
                            ->placeholder('যেমন: A-12345'),

                        Forms\Components\TextInput::make('degrees_line_bn')
                            ->label('ডিগ্রিসমূহ (বাংলা)')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('যেমন: MBBS, FCPS (Surgery), MS (Surgical Oncology)')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('current_position_bn')
                            ->label('বর্তমান পদবি ও কর্মস্থল (বাংলা)')
                            ->maxLength(255)
                            ->placeholder('যেমন: সহযোগী অধ্যাপক ও বিভাগীয় প্রধান, অনকোলজি বিভাগ')
                            ->columnSpanFull(),

                        Forms\Components\Select::make('gender')
                            ->label('লিঙ্গ')
                            ->options([
                                'male' => 'পুরুষ (Male)',
                                'female' => 'নারী (Female)',
                                'other' => 'অন্যান্য (Other)',
                            ])
                            ->default('male')
                            ->required(),

                        Forms\Components\TextInput::make('experience_years')
                            ->label('অভিজ্ঞতার বছর')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(70)
                            ->default(5),

                        Forms\Components\TextInput::make('patients_treated')
                            ->label('মোট চিকিৎসা করা রোগীর সংখ্যা')
                            ->numeric()
                            ->placeholder('যেমন: ৫০০০'),

                        Forms\Components\CheckboxList::make('doctorTypes')
                            ->label('অনকোলজিস্টের ধরন / স্পেশালিটি')
                            ->relationship('doctorTypes', 'label_bn')
                            ->required()
                            ->columns(3)
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('photo_path')
                            ->label('ডাক্তারের প্রোফাইল ছবি')
                            ->image()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('doctors')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('পরামর্শ সেবা ও ফি')
                    ->description('অনলাইন WhatsApp পরামর্শ, দ্বিতীয় মতামত ও চিকিৎসা দর্শন')
                    ->schema([
                        Forms\Components\Toggle::make('offers_second_opinion')
                            ->label('দ্বিতীয় মতামত প্রদান করেন (Offers Second Opinion)')
                            ->live()
                            ->default(false),

                        Forms\Components\TextInput::make('second_opinion_fee')
                            ->label('দ্বিতীয় মতামত ফি')
                            ->numeric()
                            ->prefix('৳')
                            ->visible(fn (Forms\Get $get) => (bool) $get('offers_second_opinion')),

                        Forms\Components\Toggle::make('offers_whatsapp')
                            ->label('WhatsApp পরামর্শ সেবা চালু আছে')
                            ->live()
                            ->default(false),

                        Forms\Components\TextInput::make('whatsapp_fee')
                            ->label('WhatsApp পরামর্শ ফি')
                            ->numeric()
                            ->prefix('৳')
                            ->visible(fn (Forms\Get $get) => (bool) $get('offers_whatsapp')),

                        Forms\Components\TextInput::make('whatsapp_response_hours')
                            ->label('WhatsApp উত্তরের গড় সময়কাল')
                            ->placeholder('যেমন: ২৪-৪৮ ঘণ্টার মধ্যে')
                            ->maxLength(60)
                            ->visible(fn (Forms\Get $get) => (bool) $get('offers_whatsapp')),

                        Forms\Components\Textarea::make('philosophy_intro_bn')
                            ->label('চিকিৎসা দর্শনের ভূমিকা (বাংলা)')
                            ->rows(2)
                            ->columnSpanFull()
                            ->placeholder('চিকিৎসা দর্শন সংক্রান্ত সংক্ষিপ্ত বার্তা...'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('যাচাই ও প্রকাশনা')
                    ->description('BMDC সরকারি যাচাই, ডাক্তারের সম্মতি ও ওয়েবসাইটে প্রদর্শনের স্ট্যাটাস')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('স্ট্যাটাস')
                            ->options([
                                DoctorStatus::Draft->value => 'খসড়া (Draft)',
                                DoctorStatus::Published->value => 'প্রকাশিত (Published)',
                                DoctorStatus::Suspended->value => 'স্থগিত (Suspended)',
                            ])
                            ->default(DoctorStatus::Draft->value)
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                if ($state === DoctorStatus::Published->value) {
                                    $set('doctor_approved_at', now()->toDateTimeString());
                                    $set('published_at', now()->toDateTimeString());
                                }
                            })
                            ->required(),

                        Forms\Components\DateTimePicker::make('doctor_approved_at')
                            ->label('ডাক্তারের সম্মতি / অনুমোদনের তারিখ')
                            ->helperText('নীতি: সম্মতি ছাড়া প্রোফাইল প্রকাশ করা যাবে না।')
                            ->default(now()),

                        Forms\Components\DateTimePicker::make('bmdc_verified_at')
                            ->label('BMDC যাচাইয়ের তারিখ ও সময়')
                            ->default(now()),

                        Forms\Components\Select::make('bmdc_verified_by')
                            ->label('BMDC যাচাইকারী কর্মকর্তা')
                            ->relationship('bmdcVerifiedBy', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('প্রকাশের তারিখ'),

                        Forms\Components\DatePicker::make('last_verified_at')
                            ->label('সর্বশেষ মাঠ যাচাইয়ের তারিখ')
                            ->default(now()),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo_path')
                    ->label('ছবি')
                    ->disk('public')
                    ->circular(),

                Tables\Columns\TextColumn::make('name_bn')
                    ->label('ডাক্তারের নাম')
                    ->weight('bold')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Doctor $record) => $record->degrees_line_bn),

                Tables\Columns\TextColumn::make('bmdc_number')
                    ->label('BMDC')
                    ->searchable(),

                Tables\Columns\TextColumn::make('doctorTypes.label_bn')
                    ->label('স্পেশালিটি')
                    ->badge()
                    ->separator(','),

                Tables\Columns\TextColumn::make('status')
                    ->label('স্ট্যাটাস')
                    ->badge()
                    ->color(fn (DoctorStatus|string $state): string => match ($state instanceof DoctorStatus ? $state->value : $state) {
                        'draft' => 'gray',
                        'published' => 'success',
                        'suspended' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state instanceof DoctorStatus ? $state->value : $state) {
                        'draft' => 'খসড়া',
                        'published' => 'প্রকাশিত',
                        'suspended' => 'স্থগিত',
                        default => $state,
                    }),

                Tables\Columns\IconColumn::make('bmdc_verified_at')
                    ->label('BMDC যাচাই')
                    ->boolean()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('last_verified_at')
                    ->label('সর্বশেষ যাচাই')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('স্ট্যাটাস')
                    ->options([
                        'draft' => 'খসড়া',
                        'published' => 'প্রকাশিত',
                        'suspended' => 'স্থগিত',
                    ]),

                Tables\Filters\SelectFilter::make('doctorTypes')
                    ->label('স্পেশালিটি')
                    ->relationship('doctorTypes', 'label_bn'),

                Tables\Filters\TernaryFilter::make('bmdc_verified_at')
                    ->label('BMDC যাচাইকৃত')
                    ->nullable(),
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
            RelationManagers\ChambersRelationManager::class,
            RelationManagers\CancerTypesRelationManager::class,
            RelationManagers\ServicesRelationManager::class,
            RelationManagers\TimelineRelationManager::class,
            RelationManagers\VideosRelationManager::class,
            RelationManagers\PatientTestimonialsRelationManager::class,
            RelationManagers\PatientStoriesRelationManager::class,
            RelationManagers\PhilosophyPointsRelationManager::class,
            RelationManagers\HospitalsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDoctors::route('/'),
            'create' => Pages\CreateDoctor::route('/create'),
            'edit' => Pages\EditDoctor::route('/{record}/edit'),
            'view' => Pages\ViewDoctor::route('/{record}'),
        ];
    }
}
