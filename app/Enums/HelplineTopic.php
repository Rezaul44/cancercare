<?php

namespace App\Enums;

enum HelplineTopic: string
{
    case ReportHelp = 'report_help';
    case FindDoctor = 'find_doctor';
    case CostQuery = 'cost_query';
    case FinancialAid = 'financial_aid';
    case CaseApplication = 'case_application';
    case Complaint = 'complaint';
    case Other = 'other';

    public function labelBn(): string
    {
        return match ($this) {
            self::ReportHelp => 'রিপোর্ট বুঝতে সাহায্য',
            self::FindDoctor => 'ডাক্তার খুঁজছেন',
            self::CostQuery => 'খরচ জানতে চান',
            self::FinancialAid => 'আর্থিক সহায়তা',
            self::CaseApplication => 'কেসের আবেদন',
            self::Complaint => 'অভিযোগ',
            self::Other => 'অন্যান্য',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ReportHelp, self::FindDoctor, self::CostQuery => 'info',
            self::FinancialAid, self::CaseApplication => 'warning',
            self::Complaint => 'danger',
            self::Other => 'gray',
        };
    }
}
