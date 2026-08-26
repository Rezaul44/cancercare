<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * ১১টি হাসপাতাল সক্ষমতা (capability)। docs/CCB_database_schema.md ধারা ২ —
 * key তালিকা হুবহু ডক থেকে।
 */
class CapabilitySeeder extends Seeder
{
    /**
     * @var list<array{key: string, label_bn: string, icon: string}>
     */
    private const CAPABILITIES = [
        ['key' => 'radiotherapy', 'label_bn' => 'রেডিওথেরাপি', 'icon' => 'radioactive'],
        ['key' => 'chemotherapy', 'label_bn' => 'কেমোথেরাপি', 'icon' => 'droplet'],
        ['key' => 'cancer_surgery', 'label_bn' => 'ক্যান্সার সার্জারি', 'icon' => 'scalpel'],
        ['key' => 'bmt', 'label_bn' => 'বোন ম্যারো ট্রান্সপ্ল্যান্ট (BMT)', 'icon' => 'bone'],
        ['key' => 'pediatric_unit', 'label_bn' => 'শিশু ক্যান্সার ইউনিট', 'icon' => 'stroller'],
        ['key' => 'palliative_care', 'label_bn' => 'উপশমকারী সেবা (Palliative Care)', 'icon' => 'heart-handshake'],
        ['key' => 'pathology_lab', 'label_bn' => 'প্যাথলজি ল্যাব', 'icon' => 'microscope'],
        ['key' => 'pet_ct', 'label_bn' => 'পিইটি-সিটি স্ক্যান (PET-CT)', 'icon' => 'scan'],
        ['key' => 'targeted_therapy', 'label_bn' => 'টার্গেটেড থেরাপি', 'icon' => 'target'],
        ['key' => 'female_oncologist', 'label_bn' => 'নারী অনকোলজিস্ট', 'icon' => 'gender-female'],
        ['key' => 'blood_bank', 'label_bn' => 'ব্লাড ব্যাংক', 'icon' => 'droplet-filled'],
    ];

    public function run(): void
    {
        foreach (self::CAPABILITIES as $index => $capability) {
            DB::table('capabilities')->updateOrInsert(
                ['key' => $capability['key']],
                [
                    'label_bn' => $capability['label_bn'],
                    'icon' => $capability['icon'],
                    'sort_order' => $index + 1,
                ]
            );
        }
    }
}
