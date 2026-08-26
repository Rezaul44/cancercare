<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * ৬টি rating_criteria। docs/CCB_database_schema.md ধারা ৪: মাঠে শুরুতে ৩টি প্রশ্ন
 * জিজ্ঞেস করা হবে (৯০ সেকেন্ডে), বাকিগুলো is_active=false রেখে পরে চালু করা যাবে।
 *
 * ডক কোন নির্দিষ্ট ৩টি তা বলেনি — এখানে প্রথম ৩টি (explains_clearly, listens_well,
 * not_rushed — সরাসরি রোগী-ডাক্তার সাক্ষাতের অভিজ্ঞতা) active ধরা হয়েছে, বাকি ৩টি
 * (follow_up_care, on_time, affordable — লজিস্টিক-নির্ভর) is_active=false। field_agent
 * টিমের সিদ্ধান্ত অনুযায়ী পরে ফ্ল্যাগ পাল্টানো যাবে।
 */
class RatingCriteriaSeeder extends Seeder
{
    /**
     * @var list<array{key: string, label_bn: string, is_active: bool}>
     */
    private const CRITERIA = [
        ['key' => 'explains_clearly', 'label_bn' => 'রোগ সম্পর্কে স্পষ্ট করে বুঝিয়েছেন', 'is_active' => true],
        ['key' => 'listens_well', 'label_bn' => 'মনোযোগ দিয়ে কথা শুনেছেন', 'is_active' => true],
        ['key' => 'not_rushed', 'label_bn' => 'তাড়াহুড়ো করে দেখেননি', 'is_active' => true],
        ['key' => 'follow_up_care', 'label_bn' => 'ফলো-আপে যত্ন নিয়েছেন', 'is_active' => false],
        ['key' => 'on_time', 'label_bn' => 'নির্ধারিত সময়ে দেখা পাওয়া গেছে', 'is_active' => false],
        ['key' => 'affordable', 'label_bn' => 'খরচ সাধ্যের মধ্যে ছিল', 'is_active' => false],
    ];

    public function run(): void
    {
        foreach (self::CRITERIA as $index => $criterion) {
            DB::table('rating_criteria')->updateOrInsert(
                ['key' => $criterion['key']],
                [
                    'label_bn' => $criterion['label_bn'],
                    'sort_order' => $index + 1,
                    'is_active' => $criterion['is_active'],
                ]
            );
        }
    }
}
