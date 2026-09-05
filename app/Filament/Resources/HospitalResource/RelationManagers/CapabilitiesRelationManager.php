<?php

namespace App\Filament\Resources\HospitalResource\RelationManagers;

use App\Enums\HospitalCapabilityStatus;
use App\Models\Capability;
use App\Models\HospitalCapability;
use App\Services\HospitalService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CapabilitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'capabilities';

    protected static ?string $title = 'চিকিৎসা সক্ষমতা ম্যাট্রিক্স (Capability Matrix)';

    protected static ?string $modelLabel = 'সক্ষমতা';

    protected static ?string $pluralModelLabel = 'সক্ষমতাসমূহ';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('capability_id')
                    ->label('সক্ষমতার ধরন')
                    ->relationship('capability', 'label_bn')
                    ->required()
                    ->disabled(fn (?HospitalCapability $record) => $record !== null)
                    ->helperText('১১টি নির্ধারিত সক্ষমতার একটি নির্বাচন করুন।'),

                Forms\Components\Radio::make('status')
                    ->label('উপলব্ধতা স্ট্যাটাস')
                    ->options([
                        HospitalCapabilityStatus::Available->value => 'উপলব্ধ (Available)',
                        HospitalCapabilityStatus::Limited->value => 'সীমিত / মাঝে মাঝে (Limited)',
                        HospitalCapabilityStatus::NotAvailable->value => 'নেই (Not Available)',
                    ])
                    ->descriptions([
                        HospitalCapabilityStatus::Available->value => 'হাসপাতালে এই সেবাটি নিয়মিত ও পূর্ণাঙ্গভাবে চালু আছে।',
                        HospitalCapabilityStatus::Limited->value => 'সেবাটি আছে তবে সীমিত বেড/মেশিন বা মাঝে মাঝে ব্যাহত হয়।',
                        HospitalCapabilityStatus::NotAvailable->value => 'হাসপাতালে এই চিকিৎসা/পরীক্ষার ব্যবস্থা নেই।',
                    ])
                    ->default(HospitalCapabilityStatus::NotAvailable->value)
                    ->required()
                    ->columns(1),

                Forms\Components\TextInput::make('machine_count')
                    ->label('মেশিন / বেড / ইউনিটের সংখ্যা')
                    ->numeric()
                    ->nullable()
                    ->placeholder('যেমন: ৩ (৩টি লিনিয়ার অ্যাক্সিলারেটর)'),

                Forms\Components\DatePicker::make('last_checked_at')
                    ->label('সর্বশেষ মাঠ যাচাইয়ের তারিখ')
                    ->default(now())
                    ->required(),

                Forms\Components\Textarea::make('detail_bn')
                    ->label('বিস্তারিত বাস্তব বিবরণ ও নোট (বাংলা)')
                    ->placeholder('যেমন: ৩টি লিনিয়ার অ্যাক্সিলারেটর ও ১টি ব্র্যাকিথেরাপি মেশিন সচল')
                    ->rows(3)
                    ->columnSpanFull()
                    ->helperText('রোগী ও পরিবারকে সুনির্দিষ্ট তথ্য দিন — কোন মেশিন সচল, কী সীমাবদ্ধতা আছে।'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('detail_bn')
            ->columns([
                Tables\Columns\TextColumn::make('capability.label_bn')
                    ->label('সক্ষমতা')
                    ->weight('bold')
                    ->sortable(),

                Tables\Columns\SelectColumn::make('status')
                    ->label('স্ট্যাটাস')
                    ->options([
                        HospitalCapabilityStatus::Available->value => 'উপলব্ধ',
                        HospitalCapabilityStatus::Limited->value => 'সীমিত',
                        HospitalCapabilityStatus::NotAvailable->value => 'নেই',
                    ])
                    ->selectablePlaceholder(false)
                    ->sortable(),

                Tables\Columns\TextColumn::make('machine_count')
                    ->label('মেশিন সংখ্যা')
                    ->alignCenter()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('detail_bn')
                    ->label('বিস্তারিত নোট')
                    ->limit(50)
                    ->wrap()
                    ->placeholder('নোট নেই'),

                Tables\Columns\TextColumn::make('last_checked_at')
                    ->label('যাচাইয়ের তারিখ')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('initializeCapabilities')
                    ->label('সব ১১টি সক্ষমতা যোগ / সিঙ্ক করুন')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->action(function (HospitalService $service) {
                        $hospital = $this->getOwnerRecord();
                        $service->initializeCapabilities($hospital);

                        Notification::make()
                            ->title('১১টি সক্ষমতা সফলভাবে যুক্ত/সিঙ্ক হয়েছে')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\CreateAction::make()
                    ->label('নতুন সক্ষমতা রেকর্ড যোগ')
                    ->visible(fn () => $this->getOwnerRecord()->capabilities()->count() < Capability::count()),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('সম্পাদনা'),
                Tables\Actions\DeleteAction::make()->label('মুছুন'),
            ])
            ->defaultSort('capability_id', 'asc')
            ->paginated(false);
    }
}
