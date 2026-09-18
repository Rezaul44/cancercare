<?php

namespace App\Filament\Resources\DoctorResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ServicesRelationManager extends RelationManager
{
    protected static string $relationship = 'services';

    protected static ?string $title = 'চিকিৎসা সেবাসমূহ (Services)';

    protected static ?string $modelLabel = 'চিকিৎসা সেবা';

    protected static ?string $pluralModelLabel = 'চিকিৎসা সেবাসমূহ';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('cancer_type_id')
                    ->label('ক্যান্সারের ধরন')
                    ->relationship('cancerType', 'name_bn')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\TextInput::make('title_bn')
                    ->label('সেবার শিরোনাম (বাংলা)')
                    ->required()
                    ->maxLength(120)
                    ->placeholder('যেমন: ব্রেস্ট কনজারভেশন সার্জারি (BCS)'),

                Forms\Components\Select::make('icon')
                    ->label('আইকন')
                    ->options([
                        'stethoscope' => '🩺 স্টেথোস্কোপ (Stethoscope)',
                        'cut' => '✂️ সার্জারি / অস্ত্রোপচার (Surgery)',
                        'vaccine' => '💉 কেমো / ভ্যাকসিন (Chemo/Vaccine)',
                        'dna' => '🧬 ডিএনএ / জেনেটিক (DNA)',
                        'microscope' => '🔬 মাইক্রোস্কোপ / বায়োপসি (Microscope)',
                        'pill' => '💊 ঔষধ / টার্গেটেড থেরাপি (Pill)',
                        'refresh' => '🔄 রিকনস্ট্রাকশন (Reconstruction)',
                        'file-description' => '📄 রিপোর্ট পর্যালোচনা (Reports)',
                        'clipboard-check' => '📋 চিকিৎসা পরিকল্পনা যাচাই (Plan Check)',
                        'shield-check' => '🛡️ স্ক্রিনিং ও প্রতিরোধ (Screening)',
                    ])
                    ->default('stethoscope')
                    ->required(),

                Forms\Components\TextInput::make('badge_text_bn')
                    ->label('ব্যাজ টেক্সট (ঐচ্ছিক)')
                    ->maxLength(60)
                    ->placeholder('যেমন: বিশেষ অভিজ্ঞতা বা ডে-কেয়ার'),

                Forms\Components\Select::make('badge_color')
                    ->label('ব্যাজের রঙ')
                    ->options([
                        'teal' => 'Teal (সবুজ-নীল)',
                        'pink' => 'Pink (গোলাপী)',
                        'blue' => 'Blue (নীল)',
                        'purple' => 'Purple (বেগুনী)',
                        'gold' => 'Gold (সোনালী)',
                        'mist' => 'Gray / Mist (ধূসর)',
                    ])
                    ->default('teal')
                    ->required(),

                Forms\Components\TextInput::make('sort_order')
                    ->label('ক্রম (Sort Order)')
                    ->numeric()
                    ->default(0),

                Forms\Components\Textarea::make('description_bn')
                    ->label('সেবার সংক্ষিপ্ত বিবরণ (বাংলা)')
                    ->required()
                    ->rows(2)
                    ->columnSpanFull()
                    ->placeholder('যেমন: স্তন সম্পূর্ণ অপসারণ না করে শুধুমাত্র টিউমার ও আশপাশের টিস্যু অপসারণ।'),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title_bn')
            ->columns([
                Tables\Columns\TextColumn::make('title_bn')
                    ->label('সেবার নাম')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('cancerType.name_bn')
                    ->label('ক্যান্সারের ধরন'),

                Tables\Columns\TextColumn::make('badge_text_bn')
                    ->label('ব্যাজ')
                    ->badge(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('ক্রম')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('নতুন সেবা যোগ'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
