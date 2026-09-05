<?php

namespace App\Enums;

enum PatientCaseVerificationStep: string
{
    case Documents = 'documents';
    case HospitalConfirm = 'hospital_confirm';
    case Identity = 'identity';
    case FieldMeeting = 'field_meeting';

    public function labelBn(): string
    {
        return match ($this) {
            self::Documents => 'চিকিৎসা কাগজপত্র যাচাই',
            self::HospitalConfirm => 'হাসপাতাল নিশ্চিতকরণ',
            self::Identity => 'পরিচয় ও এনআইডি যাচাই',
            self::FieldMeeting => 'মাঠপর্যায়ে সরাসরি সাক্ষাৎকার',
        };
    }
}
