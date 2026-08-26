<?php

namespace App\Filament\Resources\GuideResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class StepsRelationManager extends RelationManager
{
    protected static string $relationship = 'steps';

    protected static ?string $title = 'পরবর্তী পদক্ষেপ ও করণীয় (Action Steps)';

    protected static ?string $modelLabel = 'ধাপ / পদক্ষেপ';

    protected static ?string $pluralModelLabel = 'পদক্ষেপসমূহ';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('step_no')
                    ->label('ধাপ নম্বর (যেমন: ১, ২, ৩)')
                    ->numeric()
                    ->required()
                    ->default(1),

                Forms\Components\TextInput::make('title_bn')
                    ->label('পদক্ষেপের শিরোনাম (বাংলা)')
                    ->required()
                    ->maxLength(160),

                Forms\Components\Textarea::make('description_bn')
                    ->label('বিস্তারিত বিবরণ (বাংলা)')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('when_label_bn')
                    ->label('কখন করবেন (যেমন: যত দ্রুত সম্ভব, প্রথম সপ্তাহে)')
                    ->maxLength(120),

                Forms\Components\Select::make('urgency')
                    ->label('জরুরিতা (Urgency Level)')
                    ->options([
                        'urgent' => 'জরুরি (Urgent - লাল)',
                        'important' => 'গুরুত্বপূর্ণ (Important - হলুদ)',
                        'normal' => 'সাধারণ (Normal - সবুজ)',
                    ])
                    ->default('normal'),

                Forms\Components\TagsInput::make('items')
                    ->label('করণীয় চেকলিস্ট আইটেমস')
                    ->placeholder('একটি আইটেম লিখে Enter চাপুন')
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
                Tables\Columns\TextColumn::make('step_no')
                    ->label('ধাপ নং')
                    ->weight('bold')
                    ->sortable(),

                Tables\Columns\TextColumn::make('title_bn')
                    ->label('শিরোনাম')
                    ->searchable(),

                Tables\Columns\TextColumn::make('when_label_bn')
                    ->label('কখন করবেন'),

                Tables\Columns\TextColumn::make('urgency')
                    ->label('জরুরিতা')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'urgent' => 'জরুরি',
                        'important' => 'গুরুত্বপূর্ণ',
                        default => 'সাধারণ',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'urgent' => 'danger',
                        'important' => 'warning',
                        default => 'success',
                    }),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('ক্রম')
                    ->sortable(),
            ])
            ->defaultSort('sort_order', 'asc')
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('নতুন ধাপ যোগ করুন'),
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
