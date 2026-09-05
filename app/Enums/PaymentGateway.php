<?php

namespace App\Enums;

enum PaymentGateway: string
{
    case Bkash = 'bkash';
    case Nagad = 'nagad';
    case Sslcommerz = 'sslcommerz';

    public function labelBn(): string
    {
        return match ($this) {
            self::Bkash => 'বিকাশ',
            self::Nagad => 'নগদ',
            self::Sslcommerz => 'এসএসএলকমার্জ',
        };
    }
}
