<?php

namespace App\Enums;

enum SecondOpinionStatus: string
{
    case PendingPayment = 'pending_payment';
    case Submitted = 'submitted';
    case Accepted = 'accepted';
    case Answered = 'answered';
    case Refunded = 'refunded';
    case Cancelled = 'cancelled';

    public function labelBn(): string
    {
        return match ($this) {
            self::PendingPayment => 'পেমেন্ট বাকি',
            self::Submitted => 'জমা হয়েছে',
            self::Accepted => 'গৃহীত হয়েছে',
            self::Answered => 'উত্তর দেওয়া হয়েছে',
            self::Refunded => 'ফেরত দেওয়া হয়েছে',
            self::Cancelled => 'বাতিল হয়েছে',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PendingPayment => 'gray',
            self::Submitted => 'warning',
            self::Accepted => 'info',
            self::Answered => 'success',
            self::Refunded, self::Cancelled => 'danger',
        };
    }
}
