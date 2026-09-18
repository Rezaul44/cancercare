<?php

namespace App\Filament\Resources\DoctorResource\RelationManagers;

use App\Support\YouTubeHelper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class VideosRelationManager extends RelationManager
{
    protected static string $relationship = 'videos';

    protected static ?string $title = 'পরিচিতি ও শিক্ষামূলক ভিডিও (Videos)';

    protected static ?string $modelLabel = 'ভিডিও';

    protected static ?string $pluralModelLabel = 'ভিডিওসমূহ';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->label('ভিডিওর ধরন')
                    ->options([
                        'intro' => 'পরিচিতি ভিডিও (Intro Profile Video)',
                        'educational' => 'শিক্ষামূলক ভিডিও (Educational Video)',
                    ])
                    ->default('intro')
                    ->required(),

                Forms\Components\TextInput::make('title_bn')
                    ->label('ভিডিও শিরোনাম (বাংলা)')
                    ->required()
                    ->maxLength(200)
                    ->placeholder('যেমন: ডা. রফিকুল ইসলামের পরিচিতি ও চিকিৎসা পরিকল্পনা'),

                Forms\Components\TextInput::make('video_url')
                    ->label('ভিডিও লিংক (Direct YouTube Link বা Embed Link)')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('https://www.youtube.com/watch?v=... বা https://www.youtube.com/embed/...')
                    ->helperText('সরাসরি YouTube লিংক (watch, youtu.be, shorts) বা এমবেড লিংক (embed/iframe) উভয়ই সমর্থিত।')
                    ->dehydrateStateUsing(fn ($state) => YouTubeHelper::cleanUrl($state))
                    ->columnSpanFull(),

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

                Forms\Components\TextInput::make('view_count')
                    ->label('মোট ভিউ সংখ্যা')
                    ->numeric()
                    ->nullable(),

                Forms\Components\TextInput::make('produced_by')
                    ->label('প্রযোজক / চ্যানেল')
                    ->default('CancerCare Bangladesh')
                    ->maxLength(80),

                Forms\Components\FileUpload::make('thumbnail_path')
                    ->label('কাস্টম থাম্বনেইল ছবি (ঐচ্ছিক)')
                    ->image()
                    ->disk('public')
                    ->visibility('public')
                    ->directory('doctors/videos')
                    ->helperText('খালি রাখলে ইউটিউব থেকে স্বয়ংক্রিয়ভাবে থাম্বনেইল লোড হবে।'),

                Forms\Components\TextInput::make('sort_order')
                    ->label('ক্রম (Sort Order)')
                    ->numeric()
                    ->default(0),

                Forms\Components\Textarea::make('description_bn')
                    ->label('ভিডিওর বিবরণ (বাংলা)')
                    ->rows(2)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title_bn')
            ->columns([
                Tables\Columns\TextColumn::make('title_bn')
                    ->label('শিরোনাম')
                    ->weight('bold')
                    ->limit(40)
                    ->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('ধরন')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'intro' => 'পরিচিতি',
                        'educational' => 'শিক্ষামূলক',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'intro' => 'primary',
                        'educational' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('platform')
                    ->label('প্ল্যাটফর্ম')
                    ->badge(),

                Tables\Columns\TextColumn::make('duration_seconds')
                    ->label('সময়কাল')
                    ->formatStateUsing(fn ($state) => $state ? sprintf('%d:%02d মিনিট', intdiv((int)$state, 60), (int)$state % 60) : '—'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('ক্রম')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('নতুন ভিডিও যোগ'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
