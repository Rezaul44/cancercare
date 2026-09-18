<?php

namespace App\Filament\Resources;

use App\Enums\DoctorApplicationStatus;
use App\Filament\Resources\DoctorApplicationResource\Pages;
use App\Models\District;
use App\Models\DoctorApplication;
use App\Services\DoctorApplicationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class DoctorApplicationResource extends Resource
{
    protected static ?string $model = DoctorApplication::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'ডাক্তার ও কনসালটেশন';

    protected static ?string $navigationLabel = 'ডাক্তার আবেদন';

    protected static ?string $modelLabel = 'ডাক্তার আবেদন';

    protected static ?string $pluralModelLabel = 'ডাক্তার আবেদনসমূহ';

    protected static ?int $navigationSort = 2;

    private const DISK = 's3_private';

    public static function canViewAny(): bool
    {
        // 'doctor_applications.view' রোল আরও ২টা (support_agent সহ) পায়, কিন্তু এই রিসোর্স
        // শুধু super_admin ও verification_officer-এর জন্য — 'manage' পারমিশন ঠিক এই দুটো
        // রোলেই আছে (RolePermissionSeeder দ্রষ্টব্য), তাই hardcoded role-name চেকের বদলে এটা ব্যবহার করা হলো।
        return Auth::user()?->can('doctor_applications.manage') ?? false;
    }

    public static function canView(Model $record): bool
    {
        return Auth::user()?->can('doctor_applications.manage') ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('নাম')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bmdc_number')
                    ->label('BMDC নম্বর')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('জমার তারিখ')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('স্ট্যাটাস')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => DoctorApplicationStatus::from($state)->label())
                    ->color(fn (string $state): string => DoctorApplicationStatus::from($state)->color()),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('স্ট্যাটাস')
                    ->options(collect(DoctorApplicationStatus::cases())
                        ->mapWithKeys(fn (DoctorApplicationStatus $case) => [$case->value => $case->label()])
                        ->all()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('বিস্তারিত'),

                Tables\Actions\Action::make('verify')
                    ->label('যাচাই সম্পন্ন')
                    ->icon('heroicon-o-check-badge')
                    ->color('warning')
                    ->visible(fn (DoctorApplication $record): bool => ! in_array($record->status, [
                        DoctorApplicationStatus::Approved->value,
                        DoctorApplicationStatus::Rejected->value,
                    ], true))
                    ->form(self::verifyFormSchema())
                    ->fillForm(fn (DoctorApplication $record): array => [
                        'bmdc_verified' => (bool) ($record->verification_checklist['bmdc_verified'] ?? false),
                        'degree_verified' => (bool) ($record->verification_checklist['degree_verified'] ?? false),
                        'note' => $record->verification_checklist['note'] ?? null,
                    ])
                    ->action(function (DoctorApplication $record, array $data, DoctorApplicationService $service): void {
                        $service->markVerified($record, $data, Auth::user());

                        Notification::make()
                            ->title('যাচাই checklist সংরক্ষণ করা হয়েছে')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('approve')
                    ->label('অনুমোদন')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (DoctorApplication $record): bool => $record->isVerified() && ! in_array($record->status, [
                        DoctorApplicationStatus::Approved->value,
                        DoctorApplicationStatus::Rejected->value,
                    ], true))
                    ->form(self::approveFormSchema())
                    ->fillForm(fn (DoctorApplication $record): array => self::approveFormDefaults($record))
                    ->action(function (DoctorApplication $record, array $data, DoctorApplicationService $service): void {
                        $service->approve($record, $data, Auth::user());

                        Notification::make()
                            ->title('আবেদন অনুমোদিত হয়েছে এবং ডাক্তার প্রোফাইল তৈরি হয়েছে')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('বাতিল')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (DoctorApplication $record): bool => ! in_array($record->status, [
                        DoctorApplicationStatus::Approved->value,
                        DoctorApplicationStatus::Rejected->value,
                    ], true))
                    ->form(self::rejectFormSchema())
                    ->action(function (DoctorApplication $record, array $data, DoctorApplicationService $service): void {
                        $service->reject($record, $data['reason'], Auth::user());

                        Notification::make()
                            ->title('আবেদন বাতিল করা হয়েছে')
                            ->danger()
                            ->send();
                    }),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('পরিচয় ও যোগাযোগ')
                ->schema([
                    Infolists\Components\TextEntry::make('full_name')->label('নাম'),
                    Infolists\Components\TextEntry::make('bmdc_number')->label('BMDC নম্বর'),
                    Infolists\Components\TextEntry::make('phone')->label('মোবাইল'),
                    Infolists\Components\TextEntry::make('email')->label('ইমেইল'),
                    Infolists\Components\TextEntry::make('created_at')->label('জমার তারিখ')->dateTime('d M Y, h:i A'),
                    Infolists\Components\TextEntry::make('status')
                        ->label('স্ট্যাটাস')
                        ->badge()
                        ->formatStateUsing(fn (string $state): string => DoctorApplicationStatus::from($state)->label())
                        ->color(fn (string $state): string => DoctorApplicationStatus::from($state)->color()),
                ])
                ->columns(3),

            Infolists\Components\Section::make('আপলোড করা ফাইল')
                ->schema([
                    Infolists\Components\TextEntry::make('photo_link')
                        ->label('প্রোফাইল ছবি')
                        ->state(fn (DoctorApplication $record): string => self::fileLinkHtml($record->photo_path, 'ছবি দেখুন'))
                        ->html(),
                    Infolists\Components\TextEntry::make('bmdc_certificate_link')
                        ->label('BMDC সনদ')
                        ->state(fn (DoctorApplication $record): string => self::fileLinkHtml($record->bmdc_certificate_path, 'সনদ দেখুন'))
                        ->html(),
                    Infolists\Components\TextEntry::make('degree_certificate_links')
                        ->label('ডিগ্রি সনদসমূহ')
                        ->state(fn (DoctorApplication $record): string => self::degreeCertificateLinksHtml($record))
                        ->html()
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Infolists\Components\Section::make('যোগ্যতা ও পেশাগত জীবন')
                ->schema([
                    Infolists\Components\TextEntry::make('experience_years')->label('অভিজ্ঞতা (বছর)')->placeholder('—'),
                    Infolists\Components\TextEntry::make('current_position')->label('বর্তমান পদ')->placeholder('—'),
                    Infolists\Components\TextEntry::make('degrees.primary')->label('মূল ডিগ্রি'),
                    Infolists\Components\TextEntry::make('degrees.specialized')->label('বিশেষায়িত ডিগ্রি'),
                    Infolists\Components\TextEntry::make('degrees.fellowship')->label('ফেলোশিপ')->placeholder('—'),
                    Infolists\Components\RepeatableEntry::make('timeline')
                        ->label('পেশাগত জীবনের ধাপ')
                        ->schema([
                            Infolists\Components\TextEntry::make('year_label')->label('সাল'),
                            Infolists\Components\TextEntry::make('title_bn')->label('পদবি'),
                            Infolists\Components\TextEntry::make('institution_bn')->label('প্রতিষ্ঠান'),
                        ])
                        ->columns(3)
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Infolists\Components\Section::make('চেম্বার তথ্য')
                ->schema([
                    Infolists\Components\RepeatableEntry::make('chambers')
                        ->label('')
                        ->schema([
                            Infolists\Components\TextEntry::make('name_bn')->label('নাম'),
                            Infolists\Components\TextEntry::make('address_bn')->label('ঠিকানা'),
                            Infolists\Components\TextEntry::make('days_bn')->label('দিন'),
                            Infolists\Components\TextEntry::make('fee')->label('ফি')->placeholder('—'),
                        ])
                        ->columns(4),
                ]),

            Infolists\Components\Section::make('যাচাই ও পর্যালোচনা')
                ->schema([
                    Infolists\Components\TextEntry::make('verification_checklist.bmdc_verified')
                        ->label('BMDC যাচাই')
                        ->formatStateUsing(fn ($state): string => $state ? 'হ্যাঁ' : 'না')
                        ->placeholder('—'),
                    Infolists\Components\TextEntry::make('verification_checklist.degree_verified')
                        ->label('ডিগ্রি যাচাই')
                        ->formatStateUsing(fn ($state): string => $state ? 'হ্যাঁ' : 'না')
                        ->placeholder('—'),
                    Infolists\Components\TextEntry::make('verification_checklist.note')
                        ->label('যাচাই নোট')
                        ->placeholder('—')
                        ->columnSpanFull(),
                    Infolists\Components\TextEntry::make('reviewedBy.name')->label('পর্যালোচক')->placeholder('—'),
                    Infolists\Components\TextEntry::make('review_note')->label('বাতিলের কারণ')->placeholder('—')->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDoctorApplications::route('/'),
            'view' => Pages\ViewDoctorApplication::route('/{record}'),
        ];
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    public static function verifyFormSchema(): array
    {
        return [
            Forms\Components\Checkbox::make('bmdc_verified')
                ->label('BMDC নম্বর যাচাই করা হয়েছে'),
            Forms\Components\Checkbox::make('degree_verified')
                ->label('ডিগ্রি সনদ যাচাই করা হয়েছে'),
            Forms\Components\Textarea::make('note')
                ->label('নোট (ঐচ্ছিক)')
                ->rows(3),
        ];
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    public static function approveFormSchema(): array
    {
        return [
            Forms\Components\Select::make('gender')
                ->label('লিঙ্গ')
                ->options([
                    'male' => 'পুরুষ',
                    'female' => 'মহিলা',
                ])
                ->required(),
            Forms\Components\TextInput::make('experience_years')
                ->label('অভিজ্ঞতা (বছর)')
                ->numeric()
                ->minValue(0)
                ->maxValue(60)
                ->required(),
            Forms\Components\TextInput::make('current_position')
                ->label('বর্তমান পদ')
                ->maxLength(200)
                ->required(),
            Forms\Components\Repeater::make('chambers')
                ->label('চেম্বার')
                ->schema([
                    Forms\Components\TextInput::make('name_bn')->label('নাম')->required(),
                    Forms\Components\TextInput::make('address_bn')->label('ঠিকানা')->required(),
                    Forms\Components\Select::make('district_id')
                        ->label('জেলা')
                        ->options(fn () => District::orderBy('name_bn')->pluck('name_bn', 'id'))
                        ->required(),
                    Forms\Components\Select::make('type')
                        ->label('ধরন')
                        ->options([
                            'govt' => 'সরকারি',
                            'private' => 'বেসরকারি',
                            'npo' => 'এনপিও',
                        ])
                        ->required(),
                    Forms\Components\TextInput::make('fee')->label('ফি')->numeric()->minValue(0),
                    Forms\Components\TextInput::make('days_bn')->label('দিন')->required(),
                    Forms\Components\TextInput::make('time_from')
                        ->label('শুরুর সময় (HH:MM)')
                        ->placeholder('09:00')
                        ->regex('/^([01]\d|2[0-3]):[0-5]\d$/')
                        ->required(),
                    Forms\Components\TextInput::make('time_to')
                        ->label('শেষ সময় (HH:MM)')
                        ->placeholder('17:00')
                        ->regex('/^([01]\d|2[0-3]):[0-5]\d$/')
                        ->required(),
                ])
                ->columns(2)
                ->minItems(1)
                ->required(),
        ];
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    public static function rejectFormSchema(): array
    {
        return [
            Forms\Components\Textarea::make('reason')
                ->label('বাতিলের কারণ')
                ->required()
                ->rows(4),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function approveFormDefaults(DoctorApplication $record): array
    {
        return [
            'experience_years' => $record->experience_years,
            'current_position' => $record->current_position,
            'chambers' => collect($record->chambers)
                ->map(fn (array $chamber): array => [
                    'name_bn' => $chamber['name_bn'] ?? '',
                    'address_bn' => $chamber['address_bn'] ?? '',
                    'district_id' => null,
                    'type' => $chamber['type'] ?? 'private',
                    'fee' => $chamber['fee'] ?? null,
                    'days_bn' => $chamber['days_bn'] ?? '',
                    'time_from' => null,
                    'time_to' => null,
                ])
                ->all(),
        ];
    }

    private static function fileLinkHtml(?string $path, string $label): string
    {
        if (! $path) {
            return 'আপলোড করা হয়নি';
        }

        $url = e(URL::temporarySignedRoute('storage.'.self::DISK, now()->addMinutes(10), ['path' => $path], absolute: false));

        return "<a href=\"{$url}\" target=\"_blank\" rel=\"noopener\" class=\"text-primary-600 underline\">{$label}</a>";
    }

    private static function degreeCertificateLinksHtml(DoctorApplication $record): string
    {
        $paths = $record->degrees['certificate_paths'] ?? [];

        if (empty($paths)) {
            return 'আপলোড করা হয়নি';
        }

        return collect($paths)
            ->map(function (string $path, int $index): string {
                $url = e(URL::temporarySignedRoute('storage.'.self::DISK, now()->addMinutes(10), ['path' => $path], absolute: false));
                $n = $index + 1;

                return "<a href=\"{$url}\" target=\"_blank\" rel=\"noopener\" class=\"text-primary-600 underline\">সনদ #{$n}</a>";
            })
            ->implode(' &nbsp;|&nbsp; ');
    }
}
