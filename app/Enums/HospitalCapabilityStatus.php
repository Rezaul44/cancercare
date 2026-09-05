<?php

namespace App\Enums;

enum HospitalCapabilityStatus: string
{
    case Available = 'available';
    case Limited = 'limited';
    case NotAvailable = 'not_available';

    public function labelBn(): string
    {
        return match ($this) {
            self::Available => 'উপলব্ধ',
            self::Limited => 'সীমিত',
            self::NotAvailable => 'নেই',
        };
    }
}
