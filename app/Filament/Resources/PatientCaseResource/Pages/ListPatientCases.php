<?php

namespace App\Filament\Resources\PatientCaseResource\Pages;

use App\Enums\PatientCaseStatus;
use App\Filament\Resources\PatientCaseResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListPatientCases extends ListRecords
{
    protected static string $resource = PatientCaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('নতুন রোগী কেস যোগ করুন'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('সকল কেস'),
            'draft' => Tab::make('খসড়া')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', PatientCaseStatus::Draft)),
            'verifying' => Tab::make('যাচাই চলছে')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', PatientCaseStatus::Verifying)),
            'published' => Tab::make('প্রকাশিত')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', PatientCaseStatus::Published)),
            'expired' => Tab::make('মেয়াদোত্তীর্ণ')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', PatientCaseStatus::Expired)),
        ];
    }
}
