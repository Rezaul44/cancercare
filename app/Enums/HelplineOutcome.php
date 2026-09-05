<?php

namespace App\Enums;

enum HelplineOutcome: string
{
    case Resolved = 'resolved';
    case Referred = 'referred';
    case FollowUpNeeded = 'follow_up_needed';
    case CaseCreated = 'case_created';

    public function labelBn(): string
    {
        return match ($this) {
            self::Resolved => 'সমাধান হয়েছে',
            self::Referred => 'রেফার করা হয়েছে',
            self::FollowUpNeeded => 'ফলো-আপ প্রয়োজন',
            self::CaseCreated => 'কেস তৈরি হয়েছে',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Resolved => 'success',
            self::Referred => 'info',
            self::FollowUpNeeded => 'warning',
            self::CaseCreated => 'success',
        };
    }
}
