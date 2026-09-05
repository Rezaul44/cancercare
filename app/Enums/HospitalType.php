<?php

namespace App\Enums;

enum HospitalType: string
{
    case Govt = 'govt';
    case Private = 'private';
    case Npo = 'npo';

    public function labelBn(): string
    {
        return match ($this) {
            self::Govt => 'সরকারি',
            self::Private => 'বেসরকারি',
            self::Npo => 'অলাভজনক / NPO',
        };
    }
}
