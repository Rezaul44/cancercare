<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Initiated = 'initiated';
    case Success = 'success';
    case Failed = 'failed';
    case Refunded = 'refunded';

    public function labelBn(): string
    {
        return match ($this) {
            self::Initiated => 'শুরু হয়েছে',
            self::Success => 'সফল',
            self::Failed => 'ব্যর্থ',
            self::Refunded => 'ফেরত দেওয়া হয়েছে',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Initiated => 'gray',
            self::Success => 'success',
            self::Failed => 'danger',
            self::Refunded => 'warning',
        };
    }
}
