<?php

namespace App\Enums;

enum DoctorStatus: string
{
    case Draft = 'draft';
    case PendingApproval = 'pending_approval';
    case Published = 'published';
    case Suspended = 'suspended';
}
