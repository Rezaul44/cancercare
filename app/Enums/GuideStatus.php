<?php

namespace App\Enums;

enum GuideStatus: string
{
    case Draft = 'draft';
    case InReview = 'in_review';
    case Published = 'published';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'খসড়া',
            self::InReview => 'পর্যালোচনায়',
            self::Published => 'প্রকাশিত',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::InReview => 'warning',
            self::Published => 'success',
        };
    }
}
