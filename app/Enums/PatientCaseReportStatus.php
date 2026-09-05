<?php

namespace App\Enums;

enum PatientCaseReportStatus: string
{
    case New = 'new';
    case Reviewing = 'reviewing';
    case Resolved = 'resolved';
    case Dismissed = 'dismissed';

    public function labelBn(): string
    {
        return match ($this) {
            self::New => 'নতুন অভিযোগ',
            self::Reviewing => 'পর্যালোচনাধীন',
            self::Resolved => 'নিষ্পত্তিকৃত',
            self::Dismissed => 'খারিজকৃত',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'danger',
            self::Reviewing => 'warning',
            self::Resolved => 'success',
            self::Dismissed => 'gray',
        };
    }
}
