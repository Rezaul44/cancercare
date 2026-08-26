<?php

namespace App\Filament\Resources\GuideResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class FaqsRelationManager extends RelationManager
{
    protected static string $relationship = 'faqs';

    protected static ?string $title = 'সাধারণ প্রশ্নোত্তর (FAQs)';

    protected static ?string $modelLabel = 'প্রশ্নোত্তর';

    protected static ?string $pluralModelLabel = 'প্রশ্নোত্তরসমূহ';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('question_bn')
                    ->label('প্রশ্ন (বাংলা)')
                    ->required()
                    ->rows(2)
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('answer_bn')
                    ->label('উত্তর (বাংলা)')
                    ->required()
                    ->rows(4)
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
            ->recordTitleAttribute('question_bn')
            ->columns([
                Tables\Columns\TextColumn::make('question_bn')
                    ->label('প্রশ্ন')
                    ->searchable()
                    ->limit(60)
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('answer_bn')
                    ->label('উত্তর')
                    ->limit(60),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('ক্রম')
                    ->sortable(),
            ])
            ->defaultSort('sort_order', 'asc')
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('নতুন প্রশ্নোত্তর যোগ করুন'),
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
