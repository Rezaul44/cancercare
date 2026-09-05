<?php

namespace App\Enums;

enum PatientCaseDocumentType: string
{
    case Biopsy = 'biopsy';
    case TreatmentPlan = 'treatment_plan';
    case CostEstimate = 'cost_estimate';
    case Nid = 'nid';
    case Receipt = 'receipt';

    public function labelBn(): string
    {
        return match ($this) {
            self::Biopsy => 'বায়োপসি / হিস্টোপ্যাথলজি রিপোর্ট',
            self::TreatmentPlan => 'চিকিৎসা পরিকল্পনা (Treatment Plan)',
            self::CostEstimate => 'হাসপাতালের খরচ প্রাক্কলন',
            self::Nid => 'জাতীয় পরিচয়পত্র (NID)',
            self::Receipt => 'হাসপাতাল বিল / রসিদ',
        };
    }
}
