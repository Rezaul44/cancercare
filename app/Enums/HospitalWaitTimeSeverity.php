<?php

namespace App\Enums;

enum HospitalWaitTimeSeverity: string
{
    case Short = 'short';
    case Medium = 'medium';
    case Long = 'long';

    public function labelBn(): string
    {
        return match ($this) {
            self::Short => 'স্বল্প',
            self::Medium => 'মাঝারি',
            self::Long => 'দীর্ঘ',
        };
    }
}
