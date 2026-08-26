<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * বাংলাদেশের ৮টি বিভাগ ও ৬৪টি জেলা। distance_tier: ঢাকা জেলা = local; গাজীপুর,
 * নারায়ণগঞ্জ, মানিকগঞ্জ = near (ঢাকা সংলগ্ন); বাকি সব জেলা = far — cost estimator-এ
 * ব্যবহৃত (docs/CCB_database_schema.md ধারা ২)।
 */
class DivisionDistrictSeeder extends Seeder
{
    private const NEAR_DISTRICTS = ['Gazipur', 'Narayanganj', 'Manikganj'];

    private const LOCAL_DISTRICTS = ['Dhaka'];

    /**
     * @var array<int, array{name_bn: string, name_en: string, districts: list<array{name_bn: string, name_en: string}>}>
     */
    private const DIVISIONS = [
        [
            'name_bn' => 'ঢাকা', 'name_en' => 'Dhaka',
            'districts' => [
                ['name_bn' => 'ঢাকা', 'name_en' => 'Dhaka'],
                ['name_bn' => 'ফরিদপুর', 'name_en' => 'Faridpur'],
                ['name_bn' => 'গাজীপুর', 'name_en' => 'Gazipur'],
                ['name_bn' => 'গোপালগঞ্জ', 'name_en' => 'Gopalganj'],
                ['name_bn' => 'কিশোরগঞ্জ', 'name_en' => 'Kishoreganj'],
                ['name_bn' => 'মাদারীপুর', 'name_en' => 'Madaripur'],
                ['name_bn' => 'মানিকগঞ্জ', 'name_en' => 'Manikganj'],
                ['name_bn' => 'মুন্সিগঞ্জ', 'name_en' => 'Munshiganj'],
                ['name_bn' => 'নারায়ণগঞ্জ', 'name_en' => 'Narayanganj'],
                ['name_bn' => 'নরসিংদী', 'name_en' => 'Narsingdi'],
                ['name_bn' => 'রাজবাড়ী', 'name_en' => 'Rajbari'],
                ['name_bn' => 'শরীয়তপুর', 'name_en' => 'Shariatpur'],
                ['name_bn' => 'টাঙ্গাইল', 'name_en' => 'Tangail'],
            ],
        ],
        [
            'name_bn' => 'চট্টগ্রাম', 'name_en' => 'Chattogram',
            'districts' => [
                ['name_bn' => 'বান্দরবান', 'name_en' => 'Bandarban'],
                ['name_bn' => 'ব্রাহ্মণবাড়িয়া', 'name_en' => 'Brahmanbaria'],
                ['name_bn' => 'চাঁদপুর', 'name_en' => 'Chandpur'],
                ['name_bn' => 'চট্টগ্রাম', 'name_en' => 'Chattogram'],
                ['name_bn' => 'কুমিল্লা', 'name_en' => 'Cumilla'],
                ['name_bn' => 'কক্সবাজার', 'name_en' => "Cox's Bazar"],
                ['name_bn' => 'ফেনী', 'name_en' => 'Feni'],
                ['name_bn' => 'খাগড়াছড়ি', 'name_en' => 'Khagrachhari'],
                ['name_bn' => 'লক্ষ্মীপুর', 'name_en' => 'Lakshmipur'],
                ['name_bn' => 'নোয়াখালী', 'name_en' => 'Noakhali'],
                ['name_bn' => 'রাঙ্গামাটি', 'name_en' => 'Rangamati'],
            ],
        ],
        [
            'name_bn' => 'রাজশাহী', 'name_en' => 'Rajshahi',
            'districts' => [
                ['name_bn' => 'বগুড়া', 'name_en' => 'Bogura'],
                ['name_bn' => 'জয়পুরহাট', 'name_en' => 'Joypurhat'],
                ['name_bn' => 'নওগাঁ', 'name_en' => 'Naogaon'],
                ['name_bn' => 'নাটোর', 'name_en' => 'Natore'],
                ['name_bn' => 'চাঁপাইনবাবগঞ্জ', 'name_en' => 'Chapainawabganj'],
                ['name_bn' => 'পাবনা', 'name_en' => 'Pabna'],
                ['name_bn' => 'রাজশাহী', 'name_en' => 'Rajshahi'],
                ['name_bn' => 'সিরাজগঞ্জ', 'name_en' => 'Sirajganj'],
            ],
        ],
        [
            'name_bn' => 'খুলনা', 'name_en' => 'Khulna',
            'districts' => [
                ['name_bn' => 'বাগেরহাট', 'name_en' => 'Bagerhat'],
                ['name_bn' => 'চুয়াডাঙ্গা', 'name_en' => 'Chuadanga'],
                ['name_bn' => 'যশোর', 'name_en' => 'Jashore'],
                ['name_bn' => 'ঝিনাইদহ', 'name_en' => 'Jhenaidah'],
                ['name_bn' => 'খুলনা', 'name_en' => 'Khulna'],
                ['name_bn' => 'কুষ্টিয়া', 'name_en' => 'Kushtia'],
                ['name_bn' => 'মাগুরা', 'name_en' => 'Magura'],
                ['name_bn' => 'মেহেরপুর', 'name_en' => 'Meherpur'],
                ['name_bn' => 'নড়াইল', 'name_en' => 'Narail'],
                ['name_bn' => 'সাতক্ষীরা', 'name_en' => 'Satkhira'],
            ],
        ],
        [
            'name_bn' => 'বরিশাল', 'name_en' => 'Barishal',
            'districts' => [
                ['name_bn' => 'বরগুনা', 'name_en' => 'Barguna'],
                ['name_bn' => 'বরিশাল', 'name_en' => 'Barishal'],
                ['name_bn' => 'ভোলা', 'name_en' => 'Bhola'],
                ['name_bn' => 'ঝালকাঠি', 'name_en' => 'Jhalokati'],
                ['name_bn' => 'পটুয়াখালী', 'name_en' => 'Patuakhali'],
                ['name_bn' => 'পিরোজপুর', 'name_en' => 'Pirojpur'],
            ],
        ],
        [
            'name_bn' => 'সিলেট', 'name_en' => 'Sylhet',
            'districts' => [
                ['name_bn' => 'হবিগঞ্জ', 'name_en' => 'Habiganj'],
                ['name_bn' => 'মৌলভীবাজার', 'name_en' => 'Moulvibazar'],
                ['name_bn' => 'সুনামগঞ্জ', 'name_en' => 'Sunamganj'],
                ['name_bn' => 'সিলেট', 'name_en' => 'Sylhet'],
            ],
        ],
        [
            'name_bn' => 'রংপুর', 'name_en' => 'Rangpur',
            'districts' => [
                ['name_bn' => 'দিনাজপুর', 'name_en' => 'Dinajpur'],
                ['name_bn' => 'গাইবান্ধা', 'name_en' => 'Gaibandha'],
                ['name_bn' => 'কুড়িগ্রাম', 'name_en' => 'Kurigram'],
                ['name_bn' => 'লালমনিরহাট', 'name_en' => 'Lalmonirhat'],
                ['name_bn' => 'নীলফামারী', 'name_en' => 'Nilphamari'],
                ['name_bn' => 'পঞ্চগড়', 'name_en' => 'Panchagarh'],
                ['name_bn' => 'রংপুর', 'name_en' => 'Rangpur'],
                ['name_bn' => 'ঠাকুরগাঁও', 'name_en' => 'Thakurgaon'],
            ],
        ],
        [
            'name_bn' => 'ময়মনসিংহ', 'name_en' => 'Mymensingh',
            'districts' => [
                ['name_bn' => 'জামালপুর', 'name_en' => 'Jamalpur'],
                ['name_bn' => 'ময়মনসিংহ', 'name_en' => 'Mymensingh'],
                ['name_bn' => 'নেত্রকোণা', 'name_en' => 'Netrokona'],
                ['name_bn' => 'শেরপুর', 'name_en' => 'Sherpur'],
            ],
        ],
    ];

    public function run(): void
    {
        foreach (self::DIVISIONS as $division) {
            $slug = Str::slug($division['name_en']);

            DB::table('divisions')->updateOrInsert(
                ['slug' => $slug],
                [
                    'name_bn' => $division['name_bn'],
                    'name_en' => $division['name_en'],
                ]
            );

            $divisionId = DB::table('divisions')->where('slug', $slug)->value('id');

            foreach ($division['districts'] as $district) {
                $distanceTier = match (true) {
                    in_array($district['name_en'], self::LOCAL_DISTRICTS, true) => 'local',
                    in_array($district['name_en'], self::NEAR_DISTRICTS, true) => 'near',
                    default => 'far',
                };

                DB::table('districts')->updateOrInsert(
                    ['slug' => Str::slug($district['name_en'])],
                    [
                        'division_id' => $divisionId,
                        'name_bn' => $district['name_bn'],
                        'name_en' => $district['name_en'],
                        'distance_tier' => $distanceTier,
                        'has_cancer_center' => false,
                    ]
                );
            }
        }
    }
}
