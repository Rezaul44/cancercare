<?php

namespace App\Filament\Widgets;

use App\Models\Doctor;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * কোন ডাক্তারের রেটিং যাচাইকরণ কত/১০ হয়েছে — সবচেয়ে কম হয়েছে এমন ডাক্তার উপরে,
 * যাতে স্টাফ বুঝতে পারে কাকে অগ্রাধিকার দিয়ে মাঠে ডেটা সংগ্রহ করতে হবে।
 */
class RatingVerificationWidget extends BaseWidget
{
    protected static ?string $heading = 'রেটিং যাচাইকরণের অগ্রগতি';

    protected int|string|array $columnSpan = 'full';

    private const PUBLISH_THRESHOLD = 10;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Doctor::query()
                    ->published()
                    ->leftJoin('doctor_rating_summaries as drs', 'drs.doctor_id', '=', 'doctors.id')
                    ->select('doctors.*')
                    ->selectRaw('COALESCE(drs.total_count, 0) as rating_total_count')
                    ->selectRaw('COALESCE(drs.is_published, 0) as rating_is_published')
                    ->orderBy('rating_total_count')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name_bn')->label('ডাক্তার'),
                Tables\Columns\TextColumn::make('rating_total_count')
                    ->label('যাচাইকৃত রেটিং')
                    ->formatStateUsing(fn (int $state): string => $state.'/'.self::PUBLISH_THRESHOLD),
                Tables\Columns\IconColumn::make('rating_is_published')
                    ->label('প্রকাশিত')
                    ->boolean(),
            ])
            ->paginated([10, 25, 50]);
    }
}
