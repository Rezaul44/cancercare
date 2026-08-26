<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * ১৮টি cancer_type — প্রথম ৯টি is_common=true ("সবচেয়ে বেশি" ট্যাগ)।
 * docs/CCB_database_schema.md ধারা ২।
 */
class CancerTypeSeeder extends Seeder
{
    /**
     * @var list<array{name_bn: string, name_en: string, slug: string, icon: string, color_key: string, short_description_bn: string, gender_bias: string|null, is_common: bool}>
     */
    private const CANCER_TYPES = [
        ['name_bn' => 'স্তন', 'name_en' => 'Breast Cancer', 'slug' => 'breast-cancer', 'icon' => 'ribbon', 'color_key' => 'pink', 'short_description_bn' => 'স্তনের কোষে অস্বাভাবিক বৃদ্ধি থেকে সৃষ্ট ক্যান্সার।', 'gender_bias' => 'female', 'is_common' => true],
        ['name_bn' => 'ফুসফুস', 'name_en' => 'Lung Cancer', 'slug' => 'lung-cancer', 'icon' => 'lungs', 'color_key' => 'sky', 'short_description_bn' => 'ফুসফুসের কোষে সৃষ্ট ক্যান্সার, ধূমপান প্রধান ঝুঁকির কারণ।', 'gender_bias' => 'all', 'is_common' => true],
        ['name_bn' => 'মুখ', 'name_en' => 'Oral Cancer', 'slug' => 'oral-cancer', 'icon' => 'dental', 'color_key' => 'amber', 'short_description_bn' => 'মুখগহ্বর, জিহ্বা বা ঠোঁটে সৃষ্ট ক্যান্সার, তামাক-জর্দা প্রধান কারণ।', 'gender_bias' => 'all', 'is_common' => true],
        ['name_bn' => 'জরায়ু মুখ', 'name_en' => 'Cervical Cancer', 'slug' => 'cervical-cancer', 'icon' => 'gender-female', 'color_key' => 'rose', 'short_description_bn' => 'জরায়ুর মুখে সৃষ্ট ক্যান্সার, HPV সংক্রমণ প্রধান কারণ।', 'gender_bias' => 'female', 'is_common' => true],
        ['name_bn' => 'রক্ত', 'name_en' => 'Blood Cancer (Leukemia)', 'slug' => 'blood-cancer', 'icon' => 'droplet', 'color_key' => 'red', 'short_description_bn' => 'রক্ত ও অস্থিমজ্জার কোষে সৃষ্ট ক্যান্সার।', 'gender_bias' => 'all', 'is_common' => true],
        ['name_bn' => 'পাকস্থলী', 'name_en' => 'Stomach Cancer', 'slug' => 'stomach-cancer', 'icon' => 'stomach', 'color_key' => 'orange', 'short_description_bn' => 'পাকস্থলীর ভেতরের আস্তরণে সৃষ্ট ক্যান্সার।', 'gender_bias' => 'all', 'is_common' => true],
        ['name_bn' => 'কোলন', 'name_en' => 'Colon Cancer', 'slug' => 'colon-cancer', 'icon' => 'activity', 'color_key' => 'lime', 'short_description_bn' => 'বৃহদান্ত্রে সৃষ্ট ক্যান্সার, বয়স বাড়ার সাথে ঝুঁকি বাড়ে।', 'gender_bias' => 'all', 'is_common' => true],
        ['name_bn' => 'প্রোস্টেট', 'name_en' => 'Prostate Cancer', 'slug' => 'prostate-cancer', 'icon' => 'gender-male', 'color_key' => 'indigo', 'short_description_bn' => 'পুরুষের প্রোস্টেট গ্রন্থিতে সৃষ্ট ক্যান্সার।', 'gender_bias' => 'male', 'is_common' => true],
        ['name_bn' => 'শিশু', 'name_en' => 'Childhood Cancer', 'slug' => 'childhood-cancer', 'icon' => 'stroller', 'color_key' => 'teal', 'short_description_bn' => 'শিশুদের রক্ত, অস্থি বা অন্যান্য অঙ্গে সৃষ্ট ক্যান্সার।', 'gender_bias' => 'child', 'is_common' => true],

        ['name_bn' => 'লিভার', 'name_en' => 'Liver Cancer', 'slug' => 'liver-cancer', 'icon' => 'liver', 'color_key' => 'brown', 'short_description_bn' => 'যকৃতের কোষে সৃষ্ট ক্যান্সার, হেপাটাইটিস প্রধান ঝুঁকির কারণ।', 'gender_bias' => 'all', 'is_common' => false],
        ['name_bn' => 'খাদ্যনালী', 'name_en' => 'Esophageal Cancer', 'slug' => 'esophageal-cancer', 'icon' => 'throat', 'color_key' => 'yellow', 'short_description_bn' => 'খাদ্যনালীর ভেতরের আস্তরণে সৃষ্ট ক্যান্সার।', 'gender_bias' => 'all', 'is_common' => false],
        ['name_bn' => 'থাইরয়েড', 'name_en' => 'Thyroid Cancer', 'slug' => 'thyroid-cancer', 'icon' => 'neck', 'color_key' => 'cyan', 'short_description_bn' => 'গলার থাইরয়েড গ্রন্থিতে সৃষ্ট ক্যান্সার।', 'gender_bias' => 'all', 'is_common' => false],
        ['name_bn' => 'ডিম্বাশয়', 'name_en' => 'Ovarian Cancer', 'slug' => 'ovarian-cancer', 'icon' => 'egg', 'color_key' => 'fuchsia', 'short_description_bn' => 'নারীর ডিম্বাশয়ে সৃষ্ট ক্যান্সার।', 'gender_bias' => 'female', 'is_common' => false],
        ['name_bn' => 'মূত্রথলি', 'name_en' => 'Bladder Cancer', 'slug' => 'bladder-cancer', 'icon' => 'droplet-half-2', 'color_key' => 'violet', 'short_description_bn' => 'মূত্রথলির ভেতরের আস্তরণে সৃষ্ট ক্যান্সার।', 'gender_bias' => 'all', 'is_common' => false],
        ['name_bn' => 'অগ্ন্যাশয়', 'name_en' => 'Pancreatic Cancer', 'slug' => 'pancreatic-cancer', 'icon' => 'pancreas', 'color_key' => 'stone', 'short_description_bn' => 'অগ্ন্যাশয়ে সৃষ্ট ক্যান্সার, সাধারণত দেরিতে ধরা পড়ে।', 'gender_bias' => 'all', 'is_common' => false],
        ['name_bn' => 'মস্তিষ্ক', 'name_en' => 'Brain Cancer', 'slug' => 'brain-cancer', 'icon' => 'brain', 'color_key' => 'purple', 'short_description_bn' => 'মস্তিষ্ক বা তার আবরণে সৃষ্ট টিউমার/ক্যান্সার।', 'gender_bias' => 'all', 'is_common' => false],
        ['name_bn' => 'হাড়', 'name_en' => 'Bone Cancer', 'slug' => 'bone-cancer', 'icon' => 'bone', 'color_key' => 'slate', 'short_description_bn' => 'হাড়ের কোষে সৃষ্ট ক্যান্সার, তরুণদের মধ্যেও দেখা যায়।', 'gender_bias' => 'all', 'is_common' => false],
        ['name_bn' => 'ত্বক', 'name_en' => 'Skin Cancer', 'slug' => 'skin-cancer', 'icon' => 'sun', 'color_key' => 'emerald', 'short_description_bn' => 'ত্বকের কোষে সৃষ্ট ক্যান্সার, দীর্ঘ রোদে থাকা ঝুঁকির কারণ।', 'gender_bias' => 'all', 'is_common' => false],
    ];

    public function run(): void
    {
        foreach (self::CANCER_TYPES as $index => $type) {
            DB::table('cancer_types')->updateOrInsert(
                ['slug' => $type['slug']],
                [
                    'name_bn' => $type['name_bn'],
                    'name_en' => $type['name_en'],
                    'icon' => $type['icon'],
                    'color_key' => $type['color_key'],
                    'short_description_bn' => $type['short_description_bn'],
                    'gender_bias' => $type['gender_bias'],
                    'is_common' => $type['is_common'],
                    'doctor_count_cache' => 0,
                    'hospital_count_cache' => 0,
                    'guide_published' => false,
                    'sort_order' => $index + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
