<?php

namespace App\Enums;

enum HospitalFlagType: string
{
    case Positive = 'positive';
    case Warning = 'warning';
    case Negative = 'negative';

    public function labelBn(): string
    {
        return match ($this) {
            self::Positive => 'ইতিবাচক / সুবিধাজনক',
            self::Warning => 'সতর্কতা / প্রস্তুতি দরকার',
            self::Negative => 'সীমাবদ্ধতা / অনুপস্থিত',
        };
    }
}
