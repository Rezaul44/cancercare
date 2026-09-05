<?php

namespace App\Enums;

enum PatientCaseStatus: string
{
    case Draft = 'draft';
    case Verifying = 'verifying';
    case Published = 'published';
    case Expired = 'expired';
    case Fulfilled = 'fulfilled';
    case Withdrawn = 'withdrawn';

    public function labelBn(): string
    {
        return match ($this) {
            self::Draft => 'খসড়া',
            self::Verifying => 'যাচাই চলছে',
            self::Published => 'প্রকাশিত',
            self::Expired => 'মেয়াদোত্তীর্ণ',
            self::Fulfilled => 'সহায়তা সম্পন্ন',
            self::Withdrawn => 'প্রত্যাহারকৃত',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Verifying => 'warning',
            self::Published => 'success',
            self::Expired => 'danger',
            self::Fulfilled => 'info',
            self::Withdrawn => 'gray',
        };
    }
}
