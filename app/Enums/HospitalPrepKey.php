<?php

namespace App\Enums;

enum HospitalPrepKey: string
{
    case BloodBank = 'blood_bank';
    case MedicineSupply = 'medicine_supply';
    case AttendantPolicy = 'attendant_policy';
    case RecordsReturn = 'records_return';

    public function labelBn(): string
    {
        return match ($this) {
            self::BloodBank => 'ব্লাড ব্যাংক ও ডোনার',
            self::MedicineSupply => 'ওষুধের সহজলভ্যতা',
            self::AttendantPolicy => 'রোগীর সঙ্গে থাকার নিয়ম',
            self::RecordsReturn => 'রিপোর্ট ও ফাইল সংরক্ষণ',
        };
    }
}
