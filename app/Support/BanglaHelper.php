<?php

namespace App\Support;

class BanglaHelper
{
    /**
     * Convert English digits / numbers to Bengali numerals.
     */
    public static function bnNumber(int|string|null $number): string
    {
        if ($number === null || $number === '') {
            return '';
        }

        $formatted = is_numeric($number) ? number_format((float) $number) : (string) $number;
        $en = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $bn = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];

        return str_replace($en, $bn, $formatted);
    }
}
