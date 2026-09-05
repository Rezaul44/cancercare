<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DoctorRatingSubmissionResource\Pages;
use App\Models\DoctorRatingSubmission;
use App\Services\FileVaultService;
use App\Services\RatingAggregationService;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class DoctorRatingSubmissionResource extends Resource
{
    protected static ?string $model = DoctorRatingSubmission::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationLabel = 'ডাক্তার রেটিং';

    protected static ?string $modelLabel = 'রেটিং জমা';

    protected static ?string $pluralModelLabel = 'রেটিং জমাসমূহ';

    public static function canViewAny(): bool
    {
        return Auth::user()?->can('ratings.view') ?? false;
    }

    public static function canView(Model $record): bool
    {
        return Auth::user()?->can('ratings.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return false; // শুধু মাঠকর্মীর মোবাইল ফর্ম থেকে জমা হয়
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
                Tables\Columns\TextColumn::make('doctor.name_bn')
                    ->label('ডাক্তার')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('collectedBy.name')
                    ->label('মাঠকর্মী')
                    ->sortable(),
                Tables\Columns\TextColumn::make('source')
                    ->label('উৎস')
                    ->badge(),
                Tables\Columns\TextColumn::make('collected_at')
                    ->label('তারিখ')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_verified')
                    ->label('যাচাইকৃত')
                    ->boolean(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_verified')
                    ->label('যাচাই অবস্থা'),
                Tables\Filters\SelectFilter::make('doctor_id')
                    ->label('ডাক্তার')
                    ->relationship('doctor', 'name_bn')
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('বিস্তারিত'),

                Tables\Actions\Action::make('verify')
                    ->label('যাচাই সম্পন্ন')
                    ->icon('heroicon-o-check-badge')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (DoctorRatingSubmission $record): bool => ! $record->is_verified)
                    ->action(function (DoctorRatingSubmission $record, RatingAggregationService $service): void {
                        $service->verifySubmission($record, Auth::user());

                        Notification::make()
                            ->title('রেটিং যাচাই করা হয়েছে')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('জমার তথ্য')
                ->schema([
                    Infolists\Components\TextEntry::make('doctor.name_bn')->label('ডাক্তার'),
                    Infolists\Components\TextEntry::make('collectedBy.name')->label('মাঠকর্মী'),
                    Infolists\Components\TextEntry::make('source')->label('উৎস')->badge(),
                    Infolists\Components\TextEntry::make('collected_at')->label('তারিখ')->date('d M Y'),
                    Infolists\Components\TextEntry::make('is_verified')
                        ->label('যাচাইকৃত')
                        ->formatStateUsing(fn (bool $state): string => $state ? 'হ্যাঁ' : 'না'),
                    Infolists\Components\TextEntry::make('verifiedBy.name')->label('যাচাইকারী')->placeholder('—'),
                ])
                ->columns(3),

            Infolists\Components\Section::make('উত্তর ও প্রমাণ')
                ->schema([
                    Infolists\Components\KeyValueEntry::make('answers')->label('উত্তরসমূহ')->columnSpanFull(),
                    Infolists\Components\TextEntry::make('free_comment_bn')->label('অতিরিক্ত মন্তব্য')->placeholder('—')->columnSpanFull(),
                    Infolists\Components\TextEntry::make('proof_link')
                        ->label('প্রেসক্রিপশনের প্রমাণ')
                        ->state(fn (DoctorRatingSubmission $record): string => self::proofLinkHtml($record))
                        ->html(),
                ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDoctorRatingSubmissions::route('/'),
            'view' => Pages\ViewDoctorRatingSubmission::route('/{record}'),
        ];
    }

    private static function proofLinkHtml(DoctorRatingSubmission $record): string
    {
        if (! $record->proof_path) {
            return 'আপলোড করা হয়নি';
        }

        $url = e(app(FileVaultService::class)->temporaryUrl($record->proof_path));

        return "<a href=\"{$url}\" target=\"_blank\" rel=\"noopener\" class=\"text-primary-600 underline\">প্রমাণ দেখুন</a>";
    }
}
