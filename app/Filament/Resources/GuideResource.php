<?php

namespace App\Filament\Resources;

use App\Enums\GuideStatus;
use App\Filament\Resources\GuideResource\Pages;
use App\Filament\Resources\GuideResource\RelationManagers;
use App\Models\CancerType;
use App\Models\Doctor;
use App\Models\Guide;
use App\Services\GuideService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class GuideResource extends Resource
{
    protected static ?string $model = Guide::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'কন্টেন্ট ও চিকিৎসা গাইড';

    protected static ?string $navigationLabel = 'ক্যান্সার গাইড';

    protected static ?string $modelLabel = 'ক্যান্সার গাইড';

    protected static ?string $pluralModelLabel = 'ক্যান্সার গাইডসমূহ';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return Auth::user()?->can('guides.view') || Auth::user()?->can('guides.manage') || false;
    }

    public static function canView(Model $record): bool
    {
        return Auth::user()?->can('guides.view') || Auth::user()?->can('guides.manage') || false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('guides.manage') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()?->can('guides.manage') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()?->can('guides.manage') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('মূল তথ্য')
                    ->description('ক্যান্সারের ধরন, শিরোনাম ও প্রাথমিক পরিচিতি')
                    ->schema([
                        Forms\Components\Select::make('cancer_type_id')
                            ->label('ক্যান্সারের ধরন')
                            ->relationship('cancerType', 'name_bn')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->rules([
                                fn (Forms\Get $get, ?Model $record) => Rule::unique('guides', 'cancer_type_id')->ignore($record?->id),
                            ]),

                        Forms\Components\TextInput::make('title_bn')
                            ->label('গাইড শিরোনাম (বাংলা)')
                            ->required()
                            ->maxLength(200),

                        Forms\Components\Textarea::make('intro_bn')
                            ->label('গাইড পরিচিতি ও ভূমিকা (বাংলা)')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('read_minutes')
                            ->label('পড়ার সময়')
                            ->numeric()
                            ->default(5)
                            ->suffix('মিনিট')
                            ->required(),

                        Forms\Components\TextInput::make('sources_note_bn')
                            ->label('তথ্যের উৎস নোট')
                            ->default('WHO ও NCCN নির্দেশনা')
                            ->maxLength(200),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('এসইও ও মেটাডাটা')
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->label('এসইও মেটা টাইটেল')
                            ->maxLength(255),

                        Forms\Components\Textarea::make('meta_description')
                            ->label('এসইও মেটা ডেসক্রিপশন')
                            ->maxLength(255)
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),

                Forms\Components\Section::make('পর্যালোচনা ও প্রকাশনার স্ট্যাটাস')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('বর্তমান স্ট্যাটাস')
                            ->options(collect(GuideStatus::cases())
                                ->mapWithKeys(fn (GuideStatus $case) => [$case->value => $case->label()])
                                ->all())
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Select::make('reviewed_by_doctor_id')
                            ->label('অনুমোদনকারী ডাক্তার')
                            ->relationship('reviewedByDoctor', 'name_bn')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\DatePicker::make('reviewed_at')
                            ->label('অনুমোদনের তারিখ')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('প্রকাশের সময়')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cancerType.name_bn')
                    ->label('ক্যান্সার ধরন')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('title_bn')
                    ->label('শিরোনাম')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('reviewedByDoctor.name_bn')
                    ->label('পর্যালোচক ডাক্তার')
                    ->placeholder('—')
                    ->searchable(),

                Tables\Columns\TextColumn::make('reviewed_at')
                    ->label('পর্যালোচনার তারিখ')
                    ->date('d M Y')
                    ->placeholder('—')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('পর্যায় / স্ট্যাটাস')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof GuideStatus
                        ? $state->label()
                        : (GuideStatus::tryFrom($state)?->label() ?? (string) $state))
                    ->color(fn ($state): string => $state instanceof GuideStatus
                        ? $state->color()
                        : (GuideStatus::tryFrom($state)?->color() ?? 'gray'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('read_minutes')
                    ->label('সময়')
                    ->suffix(' মি.')
                    ->sortable(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('প্রকাশের তারিখ')
                    ->dateTime('d M Y, h:i A')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('সর্বশেষ আপডেট')
                    ->dateTime('d M Y, h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('পর্যায় / স্ট্যাটাস')
                    ->options(collect(GuideStatus::cases())
                        ->mapWithKeys(fn (GuideStatus $case) => [$case->value => $case->label()])
                        ->all()),

                Tables\Filters\SelectFilter::make('cancer_type_id')
                    ->label('ক্যান্সার ধরন')
                    ->relationship('cancerType', 'name_bn'),
            ])
            ->actions([
                Tables\Actions\Action::make('medicalApprove')
                    ->label('চিকিৎসা অনুমোদন')
                    ->icon('heroicon-o-check-badge')
                    ->color('info')
                    ->visible(fn (): bool => (bool) Auth::user()?->can('guides.medical_approve'))
                    ->form([
                        Forms\Components\Select::make('doctor_id')
                            ->label('অনুমোদনকারী ডাক্তার')
                            ->options(Doctor::query()->orderBy('name_bn')->pluck('name_bn', 'id'))
                            ->searchable()
                            ->required()
                            ->default(fn (Guide $record) => $record->reviewed_by_doctor_id),
                        Forms\Components\Textarea::make('note')
                            ->label('মন্তব্য (ঐচ্ছিক)')
                            ->rows(3),
                    ])
                    ->action(function (Guide $record, array $data): void {
                        app(GuideService::class)->medicalApprove($record, (int) $data['doctor_id'], $data['note'] ?? null, Auth::user());

                        Notification::make()
                            ->title('চিকিৎসা তথ্য সফলভাবে অনুমোদিত হয়েছে')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('publish')
                    ->label('প্রকাশ')
                    ->icon('heroicon-o-globe-alt')
                    ->color('success')
                    ->visible(fn (Guide $record): bool => Auth::user()?->can('guides.publish') && $record->status !== GuideStatus::Published)
                    ->disabled(fn (Guide $record): bool => empty($record->reviewed_at) || empty($record->reviewed_by_doctor_id))
                    ->tooltip(fn (Guide $record): ?string => (empty($record->reviewed_at) || empty($record->reviewed_by_doctor_id))
                        ? 'ডাক্তার কর্তৃক পর্যালোচিত না হলে প্রকাশ করা যাবে না'
                        : null
                    )
                    ->requiresConfirmation()
                    ->modalHeading('গাইড প্রকাশ নিশ্চিতকরণ')
                    ->modalDescription('আপনি কি নিশ্চিত যে এই গাইডটি ওয়েবসাইটে সর্বসাধারণের জন্য প্রকাশ করতে চান?')
                    ->action(function (Guide $record): void {
                        app(GuideService::class)->publish($record, Auth::user());

                        Notification::make()
                            ->title('গাইডটি সফলভাবে প্রকাশিত হয়েছে')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('unpublish')
                    ->label('খসড়া')
                    ->icon('heroicon-o-archive-box-arrow-down')
                    ->color('gray')
                    ->visible(fn (Guide $record): bool => Auth::user()?->can('guides.publish') && $record->status === GuideStatus::Published)
                    ->requiresConfirmation()
                    ->action(function (Guide $record): void {
                        app(GuideService::class)->unpublish($record, Auth::user());

                        Notification::make()
                            ->title('গাইডটি খসড়া হিসেবে সংরক্ষিত হয়েছে')
                            ->info()
                            ->send();
                    }),

                Tables\Actions\EditAction::make()->label('সম্পাদনা'),
                Tables\Actions\ViewAction::make()->label('দেখুন'),
                Tables\Actions\DeleteAction::make()->label('মুছুন'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('নির্বাচিতগুলো মুছুন'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TermsRelationManager::class,
            RelationManagers\StagesRelationManager::class,
            RelationManagers\StepsRelationManager::class,
            RelationManagers\MythsRelationManager::class,
            RelationManagers\FaqsRelationManager::class,
            RelationManagers\VideosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGuides::route('/'),
            'create' => Pages\CreateGuide::route('/create'),
            'edit' => Pages\EditGuide::route('/{record}/edit'),
            'view' => Pages\ViewGuide::route('/{record}'),
        ];
    }
}
