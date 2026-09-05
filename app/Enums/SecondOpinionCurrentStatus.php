<?php

namespace App\Enums;

enum SecondOpinionCurrentStatus: string
{
    case NotStarted = 'not_started';
    case Ongoing = 'ongoing';
    case Completed = 'completed';
    case Recurrence = 'recurrence';

    public function labelBn(): string
    {
        return match ($this) {
            self::NotStarted => 'চিকিৎসা শুরু হয়নি',
            self::Ongoing => 'চিকিৎসা চলছে',
            self::Completed => 'চিকিৎসা সম্পন্ন',
            self::Recurrence => 'পুনরায় দেখা দিয়েছে',
        };
    }
}
