<?php

namespace App\Filament\Resources;

use App\Enums\PatientCaseAccountType;
use App\Enums\PatientCaseAnonymity;
use App\Enums\PatientCaseDocumentType;
use App\Enums\PatientCaseGender;
use App\Enums\PatientCaseStatus;
use App\Enums\PatientCaseVerificationStatus;
use App\Enums\PatientCaseVerificationStep;
use App\Filament\Resources\PatientCaseResource\Pages;
use App\Models\PatientCase;
use App\Models\PatientCaseVerification;
use App\Services\VerificationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class PatientCaseResource extends Resource
{
    protected static ?string $model = PatientCase::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'রোগীর সহায়তা ও অনুদান';

    protected static ?string $navigationLabel = 'রোগী কেস যাচাই';

    protected static ?string $modelLabel = 'রোগী কেস';

    protected static ?string $pluralModelLabel = 'রোগী কেসসমূহ';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        return $user->can('cases.view')
            || $user->can('cases.create')
            || $user->can('cases.manage')
            || $user->can('cases.verify')
            || $user->can('cases.publish');
    }

    public static function canView(Model $record): bool
    {
        return static::canViewAny();
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();

        return $user ? ($user->can('cases.create') || $user->can('cases.manage')) : false;
    }

    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();

        return $user ? ($user->can('cases.manage') || $user->can('cases.verify')) : false;
    }

    public static function canDelete(Model $record): bool
    {
        $user = Auth::user();

        return $user ? $user->can('cases.manage') : false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('PatientCaseTabs')
                ->tabs([
                    // TAB 1: Patient Details
                    Forms\Components\Tabs\Tab::make('রোগীর তথ্য ও গল্প')
                        ->icon('heroicon-o-user')
                        ->schema([
                            Forms\Components\Section::make('পরিচিতি ও পরিচয়পত্র')
                                ->schema([
                                    Forms\Components\TextInput::make('case_code')
                                        ->label('কেস কোড')
                                        ->placeholder('স্বয়ংক্রিয়ভাবে তৈরি হবে (যেমন: CCB-2026-0001)')
                                        ->disabled()
                                        ->dehydrated(false)
                                        ->visible(fn (?PatientCase $record) => $record !== null),

                                    Forms\Components\TextInput::make('real_name')
                                        ->label('প্রকৃত নাম (এনআইডি অনুযায়ী — কঠোরভাবে গোপনীয়)')
                                        ->required()
                                        ->maxLength(150),

                                    Forms\Components\TextInput::make('display_name_bn')
                                        ->label('প্রকাশ্য নাম (বাংলায়)')
                                        ->required()
                                        ->maxLength(150),

                                    Forms\Components\Select::make('anonymity_level')
                                        ->label('নাম প্রকাশের ধরন')
                                        ->options(collect(PatientCaseAnonymity::cases())
                                            ->mapWithKeys(fn (PatientCaseAnonymity $a) => [$a->value => $a->labelBn()]))
                                        ->default(PatientCaseAnonymity::FullName->value)
                                        ->required(),

                                    Forms\Components\TextInput::make('age')
                                        ->label('বয়স (বছর)')
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(120)
                                        ->required()
                                        ->live(),

                                    Forms\Components\Select::make('gender')
                                        ->label('লিঙ্গ')
                                        ->options(collect(PatientCaseGender::cases())
                                            ->mapWithKeys(fn (PatientCaseGender $g) => [$g->value => $g->labelBn()]))
                                        ->required(),

                                    Forms\Components\FileUpload::make('photo_path')
                                        ->label('রোগীর ছবি')
                                        ->image()
                                        ->directory('patient-cases/photos')
                                        ->disk('s3_public'),

                                    Forms\Components\Toggle::make('show_photo')
                                        ->label('ছবি ওয়েবসাইটে প্রদর্শন করুন')
                                        ->default(false)
                                        ->helperText(function (Forms\Get $get): string {
                                            $age = (int) $get('age');
                                            if ($age > 0 && $age < 18) {
                                                return '⚠️ শিশু সুরক্ষা নীতি: বয়স ১৮-এর নিচে হওয়ায় ছবি প্রকাশ বাধ্যতামূলকভাবে নিষ্ক্রিয় থাকবে।';
                                            }

                                            return 'রোগীর লিখিত সম্মতি থাকলে তবেই ছবি প্রকাশ করা যাবে।';
                                        })
                                        ->disabled(fn (Forms\Get $get) => (int) $get('age') > 0 && (int) $get('age') < 18),
                                ])
                                ->columns(2),

                            Forms\Components\Section::make('চিকিৎসা ও আর্থিক প্রয়োজন')
                                ->schema([
                                    Forms\Components\Select::make('cancer_type_id')
                                        ->label('ক্যান্সারের ধরন')
                                        ->relationship('cancerType', 'name_bn')
                                        ->required()
                                        ->searchable()
                                        ->preload(),

                                    Forms\Components\TextInput::make('stage')
                                        ->label('ক্যান্সার স্টেজ / পর্যায়')
                                        ->placeholder('যেমন: স্টেজ ২, স্টেজ ৩, ইত্যাদি')
                                        ->maxLength(20),

                                    Forms\Components\Select::make('district_id')
                                        ->label('রোগীর জেলা')
                                        ->relationship('district', 'name_bn')
                                        ->required()
                                        ->searchable()
                                        ->preload(),

                                    Forms\Components\Select::make('hospital_id')
                                        ->label('চিকিৎসাধীন হাসপাতাল')
                                        ->relationship('hospital', 'name_bn')
                                        ->searchable()
                                        ->preload(),

                                    Forms\Components\TextInput::make('treating_doctor_name')
                                        ->label('চিকিৎসকের নাম')
                                        ->maxLength(150),

                                    Forms\Components\TextInput::make('amount_needed')
                                        ->label('হাসপাতাল প্রাক্কলন অনুযায়ী প্রয়োজনীয় মোট টাকা (৳)')
                                        ->numeric()
                                        ->prefix('৳')
                                        ->required(),

                                    Forms\Components\Textarea::make('story_bn')
                                        ->label('রোগীর নিজের কথা ও পারিবারিক পরিস্থিতি (বাংলা)')
                                        ->required()
                                        ->rows(5)
                                        ->columnSpanFull(),
                                ])
                                ->columns(2),
                        ]),

                    // TAB 2: Consent Form
                    Forms\Components\Tabs\Tab::make('লিখিত সম্মতিপত্র')
                        ->icon('heroicon-o-document-check')
                        ->schema([
                            Forms\Components\Section::make('সম্মতিপত্র (Consent Form)')
                                ->description('রোগী বা তার বৈধ অভিভাবকের স্বাক্ষরিত সম্মতিপত্র আপলোড করা বাধ্যতামূলক।')
                                ->schema([
                                    Forms\Components\FileUpload::make('consent_form_path')
                                        ->label('স্বাক্ষরিত সম্মতিপত্রের স্ক্যান/ছবি')
                                        ->directory('patient-cases/consent')
                                        ->disk('s3_private')
                                        ->downloadable()
                                        ->openable(),

                                    Forms\Components\DatePicker::make('consent_signed_at')
                                        ->label('সম্মতি স্বাক্ষরের তারিখ')
                                        ->default(now()),
                                ])
                                ->columns(2),
                        ]),

                    // TAB 3: 4-Step Verification Workflow
                    Forms\Components\Tabs\Tab::make('৪ ধাপের যাচাই ওয়ার্কফ্লো')
                        ->icon('heroicon-o-shield-check')
                        ->schema([
                            Forms\Components\Section::make('যাচাইকরণের ৪টি প্রধান ধাপ')
                                ->description('কেস প্রকাশের জন্য ৪টি ধাপেরই যাচাই সম্পন্ন (Done) হওয়া বাধ্যতামূলক।')
                                ->schema([
                                    Forms\Components\Repeater::make('verifications')
                                        ->relationship('verifications')
                                        ->schema([
                                            Forms\Components\Select::make('step')
                                                ->label('যাচাইকরণ ধাপ')
                                                ->options(collect(PatientCaseVerificationStep::cases())
                                                    ->mapWithKeys(fn (PatientCaseVerificationStep $s) => [$s->value => $s->labelBn()]))
                                                ->required(),

                                            Forms\Components\Select::make('status')
                                                ->label('স্ট্যাটাস')
                                                ->options(collect(PatientCaseVerificationStatus::cases())
                                                    ->mapWithKeys(fn (PatientCaseVerificationStatus $st) => [$st->value => $st->labelBn()]))
                                                ->default(PatientCaseVerificationStatus::Pending->value)
                                                ->required(),

                                            Forms\Components\Textarea::make('note_bn')
                                                ->label('যাচাই নোট / প্রমাণ বিবরণ (বাংলায়)')
                                                ->rows(2)
                                                ->columnSpanFull(),

                                            Forms\Components\Select::make('completed_by')
                                                ->label('যাচাইকারী স্টাফ')
                                                ->relationship('completedBy', 'name')
                                                ->searchable()
                                                ->preload()
                                                ->default(fn () => Auth::id()),

                                            Forms\Components\DatePicker::make('completed_at')
                                                ->label('যাচাই সম্পন্ন করার তারিখ')
                                                ->default(now()),
                                        ])
                                        ->columns(2)
                                        ->itemLabel(fn (array $state): ?string => isset($state['step'])
                                            ? (PatientCaseVerificationStep::tryFrom($state['step'])?->labelBn() ?? $state['step'])
                                            : 'যাচাই ধাপ')
                                        ->collapsible()
                                        ->defaultItems(4),
                                ]),
                        ]),

                    // TAB 4: Recipient Accounts
                    Forms\Components\Tabs\Tab::make('রোগীর অ্যাকাউন্ট')
                        ->icon('heroicon-o-banknotes')
                        ->schema([
                            Forms\Components\Section::make('সরাসরি অনুদান প্রাপ্তির অ্যাকাউন্টসমূহ')
                                ->description('রোগীর নিজস্ব বিকাশ/নগদ/ব্যাংক হিসাব। নাম এনআইডি-র সাথে যাচাইকৃত হতে হবে।')
                                ->schema([
                                    Forms\Components\Repeater::make('accounts')
                                        ->relationship('accounts')
                                        ->schema([
                                            Forms\Components\Select::make('type')
                                                ->label('অ্যাকাউন্টের ধরন')
                                                ->options(collect(PatientCaseAccountType::cases())
                                                    ->mapWithKeys(fn (PatientCaseAccountType $t) => [$t->value => $t->labelBn()]))
                                                ->required(),

                                            Forms\Components\TextInput::make('account_number')
                                                ->label('অ্যাকাউন্ট / মোবাইল নম্বর')
                                                ->required()
                                                ->maxLength(60),

                                            Forms\Components\TextInput::make('account_name')
                                                ->label('অ্যাকাউন্টধারীর নাম (NID অনুযায়ী)')
                                                ->required()
                                                ->maxLength(150),

                                            Forms\Components\TextInput::make('bank_name')
                                                ->label('ব্যাংকের নাম (ব্যাংক হলে)')
                                                ->maxLength(120),

                                            Forms\Components\TextInput::make('branch')
                                                ->label('শাখা (ব্যাংক হলে)')
                                                ->maxLength(120),

                                            Forms\Components\Toggle::make('name_verified')
                                                ->label('এনআইডি ও নাম যাচাইকৃত')
                                                ->default(false),

                                            Forms\Components\Toggle::make('is_active')
                                                ->label('সক্রিয় অ্যাকাউন্ট')
                                                ->default(true),
                                        ])
                                        ->columns(2)
                                        ->itemLabel(fn (array $state): ?string => ($state['account_name'] ?? 'অ্যাকাউন্ট') . ' (' . ($state['account_number'] ?? '') . ')')
                                        ->collapsible(),
                                ]),
                        ]),

                    // TAB 5: Cost Breakdown
                    Forms\Components\Tabs\Tab::make('খরচের বিভাজন')
                        ->icon('heroicon-o-calculator')
                        ->schema([
                            Forms\Components\Section::make('হাসপাতাল খরচের বিস্তারিত খাত')
                                ->schema([
                                    Forms\Components\Repeater::make('costs')
                                        ->relationship('costs')
                                        ->schema([
                                            Forms\Components\TextInput::make('item_bn')
                                                ->label('খরচের বিবরণ (যেমন: ৬টি কেমোথেরাপি সাইকেল)')
                                                ->required()
                                                ->maxLength(150),

                                            Forms\Components\TextInput::make('amount')
                                                ->label('টাকা (৳)')
                                                ->numeric()
                                                ->prefix('৳')
                                                ->required(),

                                            Forms\Components\TextInput::make('sort_order')
                                                ->label('ক্রম')
                                                ->numeric()
                                                ->default(0),
                                        ])
                                        ->columns(3)
                                        ->reorderable('sort_order')
                                        ->collapsible(),
                                ]),
                        ]),

                    // TAB 6: Medical & Identity Documents
                    Forms\Components\Tabs\Tab::make('চিকিৎসা ও পরিচয়পত্র নথি')
                        ->icon('heroicon-o-paper-clip')
                        ->schema([
                            Forms\Components\Section::make('প্রয়োজনীয় প্রমাণপত্র ও নথি')
                                ->description('পাবলিক ফাইলে রোগীর সংবেদনশীল তথ্য (NID নম্বর, ফোন, পূর্ণ ঠিকানা) রেড্যাক্ট (Redact) করা বাধ্যতামূলক।')
                                ->schema([
                                    Forms\Components\Repeater::make('documents')
                                        ->relationship('documents')
                                        ->schema([
                                            Forms\Components\Select::make('type')
                                                ->label('ডকুমেন্টের ধরন')
                                                ->options(collect(PatientCaseDocumentType::cases())
                                                    ->mapWithKeys(fn (PatientCaseDocumentType $dt) => [$dt->value => $dt->labelBn()]))
                                                ->required(),

                                            Forms\Components\FileUpload::make('file_path')
                                                ->label('ডকুমেন্ট ফাইল (PDF বা ছবি)')
                                                ->directory('patient-cases/documents')
                                                ->disk('s3_private')
                                                ->required()
                                                ->downloadable()
                                                ->openable(),

                                            Forms\Components\Toggle::make('is_public')
                                                ->label('ওয়েবসাইটে পাবলিকলি প্রদর্শন করুন')
                                                ->default(false),

                                            Forms\Components\Toggle::make('redacted')
                                                ->label('সংবেদনশীল তথ্য রেড্যাক্টেড / লুকানো')
                                                ->default(true),
                                        ])
                                        ->columns(2)
                                        ->collapsible(),
                                ]),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('case_code')
                    ->label('কেস কোড')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('display_name_bn')
                    ->label('রোগীর নাম')
                    ->searchable()
                    ->description(fn (PatientCase $record): string => "বয়স: {$record->age} বছর · " . ($record->district?->name_bn ?? '')),

                Tables\Columns\TextColumn::make('cancerType.name_bn')
                    ->label('ক্যান্সারের ধরন')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('amount_needed')
                    ->label('প্রয়োজনীয় টাকা')
                    ->formatStateUsing(fn ($state) => '৳ ' . number_format((int) $state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('verification_progress')
                    ->label('যাচাই অগ্রগতি')
                    ->html()
                    ->state(function (PatientCase $record, VerificationService $service): string {
                        $progress = $service->getVerificationProgress($record);
                        $done = $progress['done_count'];
                        $total = $progress['total_count'];
                        $pct = $progress['percentage'];

                        $colorClass = $pct === 100
                            ? 'bg-emerald-500'
                            : ($pct >= 50 ? 'bg-amber-500' : 'bg-rose-500');

                        return <<<HTML
                        <div class="w-28">
                            <div class="flex justify-between text-[11px] font-medium text-slate-700 dark:text-slate-300 mb-1">
                                <span>{$done}/{$total} ধাপ</span>
                                <span>{$pct}%</span>
                            </div>
                            <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
                                <div class="{$colorClass} h-2 rounded-full transition-all duration-300" style="width: {$pct}%"></div>
                            </div>
                        </div>
                        HTML;
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('স্ট্যাটাস')
                    ->badge()
                    ->formatStateUsing(fn (PatientCaseStatus|string $state): string => ($state instanceof PatientCaseStatus) ? $state->labelBn() : (PatientCaseStatus::tryFrom($state)?->labelBn() ?? $state))
                    ->color(fn (PatientCaseStatus|string $state): string => ($state instanceof PatientCaseStatus) ? $state->color() : (PatientCaseStatus::tryFrom($state)?->color() ?? 'gray')),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('আর কত দিন')
                    ->formatStateUsing(function (?Carbon $state, PatientCase $record): string {
                        if (! $state) {
                            return '—';
                        }
                        if ($record->status === PatientCaseStatus::Expired || $state->isPast()) {
                            return 'মেয়াদোত্তীর্ণ';
                        }
                        $daysLeft = (int) Carbon::now()->diffInDays($state, false);
                        if ($daysLeft <= 0) {
                            return 'আজ শেষ দিন';
                        }

                        return "আর {$daysLeft} দিন";
                    })
                    ->color(function (?Carbon $state, PatientCase $record): string {
                        if (! $state) {
                            return 'gray';
                        }
                        if ($record->status === PatientCaseStatus::Expired || $state->isPast()) {
                            return 'danger';
                        }
                        $daysLeft = (int) Carbon::now()->diffInDays($state, false);

                        return $daysLeft <= 5 ? 'danger' : 'success';
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('স্ট্যাটাস')
                    ->options(collect(PatientCaseStatus::cases())
                        ->mapWithKeys(fn (PatientCaseStatus $s) => [$s->value => $s->labelBn()])),

                Tables\Filters\SelectFilter::make('cancer_type_id')
                    ->label('ক্যান্সারের ধরন')
                    ->relationship('cancerType', 'name_bn'),

                Tables\Filters\SelectFilter::make('district_id')
                    ->label('জেলা')
                    ->relationship('district', 'name_bn'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('বিস্তারিত'),
                Tables\Actions\EditAction::make()->label('সম্পাদনা'),

                // Extend Expiration Action ("মেয়াদ বাড়াও")
                Tables\Actions\Action::make('extendDuration')
                    ->label('মেয়াদ বাড়াও')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->modalHeading('রোগী কেসের মেয়াদ ৩০ দিন বৃদ্ধি')
                    ->modalDescription('নতুন চিকিৎসার অগ্রগতি বা তথ্যের আপডেট যোগ করে কেসের মেয়াদ ৩০ দিন বাড়ানো হবে।')
                    ->visible(fn (PatientCase $record): bool =>
                        $record->status === PatientCaseStatus::Published || $record->status === PatientCaseStatus::Expired
                    )
                    ->form([
                        Forms\Components\Textarea::make('note_bn')
                            ->label('নতুন চিকিৎসার আপডেট / নোট (বাংলায়)')
                            ->placeholder('যেমন: রোগীর ৩য় সাইকেল কেমোথেরাপি সম্পন্ন হয়েছে এবং পরবর্তী চিকিৎসার জন্য সহায়তা প্রয়োজন...')
                            ->required()
                            ->rows(3),

                        Forms\Components\Toggle::make('is_public')
                            ->label('আপডেটটি পাবলিক প্রোফাইলে প্রদর্শন করুন')
                            ->default(true),
                    ])
                    ->action(function (PatientCase $record, array $data): void {
                        // 1. Create PatientCaseUpdate record
                        $record->updates()->create([
                            'update_date' => Carbon::now(),
                            'note_bn' => $data['note_bn'],
                            'is_public' => $data['is_public'] ?? true,
                            'created_by' => Auth::id(),
                        ]);

                        // 2. Extend expiration by 30 days
                        $currentExpiry = $record->expires_at;
                        if ($currentExpiry && $currentExpiry->isFuture()) {
                            $record->expires_at = $currentExpiry->copy()->addDays(30);
                        } else {
                            $record->expires_at = Carbon::now()->addDays(30);
                        }

                        // If expired, reactivate to published
                        if ($record->status === PatientCaseStatus::Expired) {
                            $record->status = PatientCaseStatus::Published;
                        }

                        $record->save();

                        Notification::make()
                            ->title('কেসের মেয়াদ ৩০ দিন বাড়ানো হয়েছে')
                            ->body("কেস: {$record->case_code}। নতুন মেয়াদ: " . $record->expires_at->translatedFormat('d F Y'))
                            ->success()
                            ->send();
                    }),

                // 4-Step Verification Action
                Tables\Actions\Action::make('verifySteps')
                    ->label('ধাপসমূহ যাচাই')
                    ->icon('heroicon-o-shield-check')
                    ->color('warning')
                    ->visible(fn (): bool => Auth::user()?->can('cases.verify') || Auth::user()?->can('cases.manage') || false)
                    ->form(function (PatientCase $record): array {
                        $schema = [];
                        $existingVerifications = $record->verifications->keyBy(function ($v) {
                            return ($v->step instanceof PatientCaseVerificationStep) ? $v->step->value : (string) $v->step;
                        });

                        foreach (VerificationService::REQUIRED_STEPS as $stepKey) {
                            $stepEnum = PatientCaseVerificationStep::tryFrom($stepKey);
                            $stepLabel = $stepEnum ? $stepEnum->labelBn() : $stepKey;
                            $existing = $existingVerifications->get($stepKey);

                            $schema[] = Forms\Components\Section::make($stepLabel)
                                ->schema([
                                    Forms\Components\Select::make("steps.{$stepKey}.status")
                                        ->label('স্ট্যাটাস')
                                        ->options(collect(PatientCaseVerificationStatus::cases())
                                            ->mapWithKeys(fn (PatientCaseVerificationStatus $st) => [$st->value => $st->labelBn()]))
                                        ->default($existing?->status?->value ?? ($existing?->status ?? PatientCaseVerificationStatus::Pending->value))
                                        ->required(),

                                    Forms\Components\Textarea::make("steps.{$stepKey}.note_bn")
                                        ->label('যাচাই নোট / প্রমাণ বিবরণ')
                                        ->default($existing?->note_bn)
                                        ->rows(2),
                                ])
                                ->compact();
                        }

                        return $schema;
                    })
                    ->action(function (PatientCase $record, array $data): void {
                        $stepsData = $data['steps'] ?? [];

                        foreach ($stepsData as $stepKey => $stepInfo) {
                            PatientCaseVerification::updateOrCreate(
                                [
                                    'patient_case_id' => $record->id,
                                    'step' => $stepKey,
                                ],
                                [
                                    'status' => $stepInfo['status'] ?? PatientCaseVerificationStatus::Pending->value,
                                    'note_bn' => $stepInfo['note_bn'] ?? null,
                                    'completed_by' => Auth::id(),
                                    'completed_at' => ($stepInfo['status'] === PatientCaseVerificationStatus::Done->value) ? now() : null,
                                ]
                            );
                        }

                        Notification::make()
                            ->title('যাচাইকরণের তথ্য হালনাগাদ করা হয়েছে')
                            ->success()
                            ->send();
                    }),

                // Publish Action
                Tables\Actions\Action::make('publish')
                    ->label('প্রকাশ করুন')
                    ->icon('heroicon-o-globe-alt')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('রোগী কেস প্রকাশনা অনুমোদন')
                    ->modalDescription(function (PatientCase $record, VerificationService $service): string {
                        $missing = $service->getMissingRequirements($record);
                        if (! empty($missing)) {
                            return '⚠️ সতর্কতা: কেসটি এখনো প্রকাশের জন্য প্রস্তুত নয়। অপূর্ণ শর্ত: ' . implode(', ', $missing);
                        }

                        return 'আপনি কি এই রোগীর কেসটি ওয়েবসাইটে প্রকাশের জন্য অনুমোদন দিতে চান? প্রকাশের পর এটি ৩০ দিন পর্যন্ত সক্রিয় থাকবে।';
                    })
                    ->visible(function (PatientCase $record): bool {
                        $user = Auth::user();
                        if (! $user || ! $user->can('cases.publish')) {
                            return false;
                        }

                        return $record->status !== PatientCaseStatus::Published;
                    })
                    ->action(function (PatientCase $record, VerificationService $service): void {
                        try {
                            $service->publishCase($record, Auth::user());

                            Notification::make()
                                ->title('রোগীর কেস সফলভাবে প্রকাশিত হয়েছে')
                                ->body("কেস কোড: {$record->case_code}। মেয়াদ: ৩০ দিন।")
                                ->success()
                                ->send();
                        } catch (\DomainException $e) {
                            Notification::make()
                                ->title('কেস প্রকাশ করা যায়নি')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPatientCases::route('/'),
            'create' => Pages\CreatePatientCase::route('/create'),
            'view' => Pages\ViewPatientCase::route('/{record}'),
            'edit' => Pages\EditPatientCase::route('/{record}/edit'),
        ];
    }
}
