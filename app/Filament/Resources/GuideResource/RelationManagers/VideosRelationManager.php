<?php

namespace App\Filament\Resources\GuideResource\RelationManagers;

use App\Models\Doctor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class VideosRelationManager extends RelationManager
{
    protected static string $relationship = 'videos';

    protected static ?string $title = 'ডাক্তারের ভিডিও (Doctor Videos)';

    protected static ?string $modelLabel = 'ভিডিও';

    protected static ?string $pluralModelLabel = 'ভিডিওসমূহ';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title_bn')
                    ->label('ভিডিও শিরোনাম (বাংলা)')
                    ->required()
                    ->maxLength(200),

                Forms\Components\TextInput::make('video_url')
                    ->label('ভিডিও লিঙ্ক (YouTube URL)')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('platform')
                    ->label('প্ল্যাটফর্ম')
                    ->options([
                        'youtube' => 'YouTube',
                        'facebook' => 'Facebook',
                    ])
                    ->default('youtube')
                    ->required(),

                Forms\Components\Select::make('doctor_id')
                    ->label('বক্তা ডাক্তার')
                    ->options(Doctor::query()->orderBy('name_bn')->pluck('name_bn', 'id'))
                    ->searchable()
                    ->nullable(),

                Forms\Components\TextInput::make('duration_seconds')
                    ->label('সময়কাল (সেকেন্ডে, যেমন: ১২০)')
                    ->numeric()
                    ->default(0),

                Forms\Components\Textarea::make('description_bn')
                    ->label('সংক্ষিপ্ত বিবরণ (বাংলা)')
                    ->rows(2)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('sort_order')
                    ->label('ক্রমিক নম্বর')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title_bn')
            ->columns([
                Tables\Columns\TextColumn::make('title_bn')
                    ->label('ভিডিও শিরোনাম')
                    ->searchable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('doctor.name_bn')
                    ->label('বক্তা ডাক্তার')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('platform')
                    ->label('প্ল্যাটফর্ম')
                    ->badge(),

                Tables\Columns\TextColumn::make('duration_seconds')
                    ->label('সময় (সে.)'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('ক্রম')
                    ->sortable(),
            ])
            ->defaultSort('sort_order', 'asc')
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('নতুন ভিডিও যোগ করুন'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('সম্পাদনা'),
                Tables\Actions\DeleteAction::make()->label('মুছুন'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('নির্বাচিতগুলো মুছুন'),
                ]),
            ]);
    }
}
