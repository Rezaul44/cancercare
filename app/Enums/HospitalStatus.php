<?php

namespace App\Enums;

enum HospitalStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Suspended = 'suspended';

    public function labelBn(): string
    {
        return match ($this) {
            self::Draft => 'খসড়া',
            self::Published => 'প্রকাশিত',
            self::Suspended => 'স্থগিত',
        };
    }
}
