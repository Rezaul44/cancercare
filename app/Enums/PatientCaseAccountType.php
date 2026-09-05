<?php

namespace App\Enums;

enum PatientCaseAccountType: string
{
    case Bkash = 'bkash';
    case Nagad = 'nagad';
    case Rocket = 'rocket';
    case Bank = 'bank';

    public function labelBn(): string
    {
        return match ($this) {
            self::Bkash => 'বিকাশ (bKash)',
            self::Nagad => 'নগদ (Nagad)',
            self::Rocket => 'রকেট (Rocket)',
            self::Bank => 'ব্যাংক হিসাব (Bank Account)',
        };
    }
}
