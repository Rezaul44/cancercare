<?php

namespace App\Enums;

enum DoctorApplicationStatus: string
{
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case CallScheduled = 'call_scheduled';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'জমা দেওয়া হয়েছে',
            self::UnderReview => 'পর্যালোচনাধীন',
            self::CallScheduled => 'কল নির্ধারিত',
            self::Approved => 'অনুমোদিত',
            self::Rejected => 'বাতিল',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Submitted => 'gray',
            self::UnderReview => 'warning',
            self::CallScheduled => 'info',
            self::Approved => 'success',
            self::Rejected => 'danger',
        };
    }
}
