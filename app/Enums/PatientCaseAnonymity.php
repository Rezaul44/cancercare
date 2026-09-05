<?php

namespace App\Enums;

enum PatientCaseAnonymity: string
{
    case FullName = 'full_name';
    case Partial = 'partial';
    case ChangedName = 'changed_name';
    case InitialsOnly = 'initials_only';

    public function labelBn(): string
    {
        return match ($this) {
            self::FullName => 'পূর্ণ নাম প্রকাশ',
            self::Partial => 'আংশিক নাম',
            self::ChangedName => 'পরিবর্তিত নাম',
            self::InitialsOnly => 'শুধু আদ্যক্ষর (Initials)',
        };
    }
}
