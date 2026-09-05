<?php

namespace App\Enums;

enum DoctorPayoutStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';

    public function labelBn(): string
    {
        return match ($this) {
            self::Pending => 'বাকি',
            self::Paid => 'পরিশোধিত',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Paid => 'success',
        };
    }
}
