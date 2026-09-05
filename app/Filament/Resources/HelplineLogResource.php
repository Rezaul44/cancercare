<?php

namespace App\Filament\Resources;

use App\Enums\HelplineChannel;
use App\Enums\HelplineOutcome;
use App\Enums\HelplineTopic;
use App\Filament\Resources\HelplineLogResource\Pages;
use App\Models\CancerType;
use App\Models\District;
use App\Models\HelplineLog;
use App\Models\PatientCase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HelplineLogResource extends Resource
{
    protected static ?string $model = HelplineLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-phone';

    protected static ?string $navigationLabel = 'হেল্পলাইন';

    protected static ?string $modelLabel = 'কল লগ';

    protected static ?string $pluralModelLabel = 'কল লগসমূহ';

    public static function canViewAny(): bool
    {
        return Auth::user()?->can('helpline.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('helpline.manage') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()?->can('helpline.manage') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()?->can('helpline.manage') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('channel')
                ->label('মাধ্যম')
                ->options(collect(HelplineChannel::cases())->mapWithKeys(fn ($c) => [$c->value => $c->labelBn()]))
                ->required(),
            Forms\Components\TextInput::make('caller_name')
                ->label('কলারের নাম')
                ->maxLength(150),
            Forms\Components\TextInput::make('caller_phone')
                ->label('মোবাইল নম্বর')
                ->required()
                ->maxLength(20),
            Forms\Components\Select::make('district_id')
                ->label('জেলা')
                ->options(fn () => District::orderBy('name_bn')->pluck('name_bn', 'id'))
                ->searchable(),
            Forms\Components\Select::make('cancer_type_id')
                ->label('ক্যান্সারের ধরন')
                ->options(fn () => CancerType::orderBy('sort_order')->pluck('name_bn', 'id'))
                ->searchable(),
            Forms\Components\Select::make('topic')
                ->label('বিষয়')
                ->options(collect(HelplineTopic::cases())->mapWithKeys(fn ($t) => [$t->value => $t->labelBn()]))
                ->required(),
            Forms\Components\Textarea::make('summary_bn')
                ->label('সারাংশ')
                ->required()
                ->rows(3)
                ->columnSpanFull(),
            Forms\Components\Select::make('outcome')
                ->label('ফলাফল')
                ->options(collect(HelplineOutcome::cases())->mapWithKeys(fn ($o) => [$o->value => $o->labelBn()]))
                ->required(),
            Forms\Components\Select::make('linked_case_id')
                ->label('সংযুক্ত কেস (যদি থাকে)')
                ->options(fn () => PatientCase::orderByDesc('created_at')->limit(50)->pluck('case_code', 'id'))
                ->searchable(),
            Forms\Components\DatePicker::make('follow_up_at')
                ->label('ফলো-আপের তারিখ'),
            Forms\Components\TextInput::make('call_duration_minutes')
                ->label('কলের দৈর্ঘ্য (মিনিট)')
                ->numeric()
                ->minValue(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('তারিখ')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('caller_phone')
                    ->label('মোবাইল')
                    ->searchable(),
                Tables\Columns\TextColumn::make('topic')
                    ->label('বিষয়')
                    ->badge()
                    ->formatStateUsing(fn (HelplineTopic $state): string => $state->labelBn())
                    ->color(fn (HelplineTopic $state): string => $state->color()),
                Tables\Columns\TextColumn::make('outcome')
                    ->label('ফলাফল')
                    ->badge()
                    ->formatStateUsing(fn (HelplineOutcome $state): string => $state->labelBn())
                    ->color(fn (HelplineOutcome $state): string => $state->color()),
                Tables\Columns\TextColumn::make('handler.name')
                    ->label('স্টাফ'),
                Tables\Columns\TextColumn::make('follow_up_at')
                    ->label('ফলো-আপ')
                    ->date('d M Y')
                    ->placeholder('—'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('topic')
                    ->label('বিষয়')
                    ->options(collect(HelplineTopic::cases())->mapWithKeys(fn ($t) => [$t->value => $t->labelBn()])),
                Tables\Filters\SelectFilter::make('outcome')
                    ->label('ফলাফল')
                    ->options(collect(HelplineOutcome::cases())->mapWithKeys(fn ($o) => [$o->value => $o->labelBn()])),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('সম্পাদনা'),

                Tables\Actions\Action::make('createCase')
                    ->label('রোগীর কেস তৈরি করুন')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->visible(fn (HelplineLog $record): bool => $record->linked_case_id === null)
                    ->form(self::createCaseFormSchema())
                    ->fillForm(fn (HelplineLog $record): array => [
                        'cancer_type_id' => $record->cancer_type_id,
                        'district_id' => $record->district_id,
                    ])
                    ->action(function (HelplineLog $record, array $data): void {
                        DB::transaction(function () use ($record, $data): void {
                            $case = PatientCase::create([
                                'real_name' => $data['real_name'],
                                'display_name_bn' => $data['display_name_bn'],
                                'age' => $data['age'],
                                'gender' => $data['gender'],
                                'cancer_type_id' => $data['cancer_type_id'],
                                'district_id' => $data['district_id'],
                                'story_bn' => $data['story_bn'],
                                'amount_needed' => $data['amount_needed'],
                                'status' => 'draft',
                                'created_by' => Auth::id(),
                            ]);

                            $record->update([
                                'linked_case_id' => $case->id,
                                'outcome' => 'case_created',
                            ]);
                        });

                        Notification::make()
                            ->title('রোগীর কেস তৈরি হয়েছে — এখন যাচাইকরণ ওয়ার্কফ্লোতে আছে')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHelplineLogs::route('/'),
            'create' => Pages\CreateHelplineLog::route('/create'),
            'edit' => Pages\EditHelplineLog::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    private static function createCaseFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('real_name')->label('রোগীর প্রকৃত নাম')->required()->maxLength(150),
            Forms\Components\TextInput::make('display_name_bn')->label('প্রদর্শিত নাম')->required()->maxLength(150),
            Forms\Components\TextInput::make('age')->label('বয়স')->numeric()->required()->minValue(0)->maxValue(120),
            Forms\Components\Select::make('gender')
                ->label('লিঙ্গ')
                ->options(['male' => 'পুরুষ', 'female' => 'মহিলা', 'other' => 'অন্যান্য'])
                ->required(),
            Forms\Components\Select::make('cancer_type_id')
                ->label('ক্যান্সারের ধরন')
                ->options(fn () => CancerType::orderBy('sort_order')->pluck('name_bn', 'id'))
                ->required(),
            Forms\Components\Select::make('district_id')
                ->label('জেলা')
                ->options(fn () => District::orderBy('name_bn')->pluck('name_bn', 'id'))
                ->required(),
            Forms\Components\Textarea::make('story_bn')->label('রোগীর কথা')->required()->rows(4)->columnSpanFull(),
            Forms\Components\TextInput::make('amount_needed')->label('প্রয়োজনীয় টাকা (৳)')->numeric()->required()->minValue(0),
        ];
    }
}
