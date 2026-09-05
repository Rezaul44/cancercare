<?php

namespace App\Filament\Resources\HospitalResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class VideosRelationManager extends RelationManager
{
    protected static string $relationship = 'videos';

    protected static ?string $title = 'হাসপাতাল পরিচিতি ও দিকনির্দেশনা ভিডিও (Videos)';

    protected static ?string $modelLabel = 'ভিডিও';

    protected static ?string $pluralModelLabel = 'ভিডিওসমূহ';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title_bn')
                    ->label('ভিডিও শিরোনাম (বাংলা)')
                    ->required()
                    ->maxLength(200)
                    ->placeholder('যেমন: জাতীয় ক্যান্সার হাসপাতালে প্রথম দিন: টিকিট কাটা থেকে ডাক্তার দেখানো'),

                Forms\Components\TextInput::make('video_url')
                    ->label('ভিডিও লিংক (URL)')
                    ->required()
                    ->url()
                    ->maxLength(255)
                    ->placeholder('https://www.youtube.com/watch?v=...'),

                Forms\Components\Select::make('platform')
                    ->label('প্ল্যাটফর্ম')
                    ->options([
                        'youtube' => 'YouTube',
                        'facebook' => 'Facebook',
                    ])
                    ->default('youtube')
                    ->required(),

                Forms\Components\TextInput::make('duration_seconds')
                    ->label('সময়কাল (সেকেন্ডে)')
                    ->numeric()
                    ->default(180)
                    ->helperText('যেমন: ১৮০ সেকেন্ড (৩ মিনিট)'),

                Forms\Components\TextInput::make('produced_by')
                    ->label('প্রযোজক / উৎস')
                    ->default('CancerCare Bangladesh')
                    ->required()
                    ->maxLength(80)
                    ->helperText('শুধু CCB-র নিজস্ব নিরপেক্ষ দিকনির্দেশনামূলক ভিডিও অনুমোদিত।'),

                Forms\Components\TextInput::make('sort_order')
                    ->label('ক্রম (Sort Order)')
                    ->numeric()
                    ->default(0),

                Forms\Components\Textarea::make('description_bn')
                    ->label('ভিডিও বিবরণ (বাংলা)')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title_bn')
            ->columns([
                Tables\Columns\TextColumn::make('title_bn')
                    ->label('শিরোনাম')
                    ->weight('semibold')
                    ->limit(40),

                Tables\Columns\TextColumn::make('platform')
                    ->label('প্ল্যাটফর্ম')
                    ->badge(),

                Tables\Columns\TextColumn::make('duration_formatted')
                    ->label('সময়কাল')
                    ->state(fn ($record) => $record->duration_seconds > 0 ? gmdate('i:s', $record->duration_seconds).' মিনিট' : '—'),

                Tables\Columns\TextColumn::make('produced_by')
                    ->label('প্রযোজক'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('ক্রম')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('নতুন ভিডিও যোগ'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('সম্পাদনা'),
                Tables\Actions\DeleteAction::make()->label('মুছুন'),
            ])
            ->defaultSort('sort_order', 'asc');
    }
}
