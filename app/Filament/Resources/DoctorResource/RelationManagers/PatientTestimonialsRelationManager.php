<?php

namespace App\Filament\Resources\DoctorResource\RelationManagers;

use App\Support\YouTubeHelper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PatientTestimonialsRelationManager extends RelationManager
{
    protected static string $relationship = 'patientTestimonials';

    protected static ?string $title = 'রোগীদের ভিডিও অভিজ্ঞতা (Video Testimonials)';

    protected static ?string $modelLabel = 'ভিডিও অভিজ্ঞতা';

    protected static ?string $pluralModelLabel = 'ভিডিও অভিজ্ঞতাসমূহ';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('anonymized_label_bn')
                    ->label('রোগীর নাম / লেবেল (বাংলা)')
                    ->required()
                    ->maxLength(120)
                    ->placeholder('যেমন: শামীমা আক্তার বা রোগী "ক"'),

                Forms\Components\Select::make('cancer_type_id')
                    ->label('ক্যান্সারের ধরন')
                    ->relationship('cancerType', 'name_bn')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\Select::make('stage')
                    ->label('ক্যান্সারের পর্যায় (Stage)')
                    ->options([
                        '1' => 'স্টেজ ১',
                        '2' => 'স্টেজ ২',
                        '3' => 'স্টেজ ৩',
                        '4' => 'স্টেজ ৪',
                        'unknown' => 'অজানা / প্রযোজ্য নয়',
                    ])
                    ->nullable(),

                Forms\Components\TextInput::make('outcome_bn')
                    ->label('চিকিৎসার ফলাফল (বাংলা)')
                    ->required()
                    ->maxLength(120)
                    ->placeholder('যেমন: সম্পূর্ণ সুস্থ / সফল অস্ত্রোপচার সম্পন্ন'),

                Forms\Components\TextInput::make('year')
                    ->label('চিকিৎসার সাল')
                    ->numeric()
                    ->default(date('Y'))
                    ->required(),

                Forms\Components\TextInput::make('video_url')
                    ->label('ইউটিউব ভিডিও লিংক (Direct Link বা Embed Link)')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('https://www.youtube.com/watch?v=... বা https://www.youtube.com/embed/...')
                    ->helperText('সরাসরি YouTube লিংক বা এমবেড লিংক গ্রহণ করে।')
                    ->dehydrateStateUsing(fn ($state) => YouTubeHelper::cleanUrl($state))
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('duration_seconds')
                    ->label('সময়কাল (সেকেন্ডে)')
                    ->numeric()
                    ->default(120)
                    ->helperText('যেমন: ১২০ সেকেন্ড (২ মিনিট)'),

                Forms\Components\Select::make('thumbnail_color_key')
                    ->label('থাম্বনেইল ব্যাকগ্রাউন্ড থিম')
                    ->options([
                        'teal' => 'Teal (সবুজ-নীল)',
                        'pink' => 'Pink (গোলাপী)',
                        'blue' => 'Blue (নীল)',
                        'purple' => 'Purple (বেগুনী)',
                        'gold' => 'Gold (সোনালী)',
                    ])
                    ->default('teal')
                    ->required(),

                Forms\Components\TextInput::make('sort_order')
                    ->label('ক্রম (Sort Order)')
                    ->numeric()
                    ->default(0),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('anonymized_label_bn')
            ->columns([
                Tables\Columns\TextColumn::make('anonymized_label_bn')
                    ->label('রোগীর লেবেল')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('cancerType.name_bn')
                    ->label('ক্যান্সারের ধরন'),

                Tables\Columns\TextColumn::make('outcome_bn')
                    ->label('ফলাফল')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('year')
                    ->label('সাল'),

                Tables\Columns\TextColumn::make('duration_seconds')
                    ->label('সময়কাল')
                    ->formatStateUsing(fn ($state) => $state ? sprintf('%d:%02d মিনিট', intdiv((int)$state, 60), (int)$state % 60) : '—'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('নতুন অভিজ্ঞতা যোগ'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
