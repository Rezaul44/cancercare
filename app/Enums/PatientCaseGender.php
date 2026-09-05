<?php

namespace App\Enums;

enum PatientCaseGender: string
{
    case Male = 'male';
    case Female = 'female';
    case Other = 'other';

    public function labelBn(): string
    {
        return match ($this) {
            self::Male => 'পুরুষ',
            self::Female => 'নারী',
            self::Other => 'অন্যান্য',
        };
    }
}
