<?php

namespace App\Enums;

enum PatientCaseVerificationStatus: string
{
    case Pending = 'pending';
    case Done = 'done';
    case Failed = 'failed';

    public function labelBn(): string
    {
        return match ($this) {
            self::Pending => 'অপেক্ষমান',
            self::Done => 'যাচাই সম্পন্ন',
            self::Failed => 'যাচাই ব্যর্থ',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Done => 'success',
            self::Failed => 'danger',
        };
    }
}
