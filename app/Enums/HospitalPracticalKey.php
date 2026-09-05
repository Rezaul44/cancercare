<?php

namespace App\Enums;

enum HospitalPracticalKey: string
{
    case Documents = 'documents';
    case Timing = 'timing';
    case Accommodation = 'accommodation';
    case Transport = 'transport';
    case FinancialAid = 'financial_aid';

    public function labelBn(): string
    {
        return match ($this) {
            self::Documents => 'প্রয়োজনীয় কাগজপত্র',
            self::Timing => 'টিকিট ও ডাক্তার দেখানোর সময়সূচি',
            self::Accommodation => 'থাকা ও আত্মীয়দের ব্যবস্থা',
            self::Transport => 'যাতায়াত ও অবস্থান',
            self::FinancialAid => 'আর্থিক সহায়তা ও সমাজকল্যাণ',
        };
    }
}
