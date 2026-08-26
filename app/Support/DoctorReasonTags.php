<?php

namespace App\Support;

use App\Models\Doctor;

/**
 * docs/prototypes/doctor_directory.html-এর .dc-why ট্যাগ ("কেন এসেছে")। DoctorRankingService::query()
 * ইতিমধ্যে specialty_score/district_score/rating_score কলাম আর cancerTypes/chambers.district relation
 * eager-load করে রাখে — এখানে নতুন কোনো query চালানো হয় না, শুধু সেই ডেটা পড়ে ট্যাগ বানানো হয়।
 */
class DoctorReasonTags
{
    private const MAX_TAGS = 3;

    /**
     * @param  array{cancer_type_id?: int|null, district_id?: int|null}  $filters
     * @return list<string>
     */
    public static function for(Doctor $doctor, array $filters): array
    {
        $tags = [];

        if (($doctor->specialty_score ?? 0) > 0) {
            $tags[] = self::specialtyTag($doctor, $filters['cancer_type_id'] ?? null);
        }

        if (($doctor->district_score ?? 0) > 0) {
            $tags[] = self::districtTag($doctor, $filters['district_id'] ?? null);
        }

        if (($doctor->rating_score ?? 0) > 0) {
            $tags[] = 'উচ্চ রেটিং';
        }

        if ($doctor->offers_whatsapp) {
            $tags[] = 'WhatsApp পরামর্শ';
        }

        if ($doctor->offers_second_opinion) {
            $tags[] = 'দ্বিতীয় মতামত দেন';
        }

        return array_slice($tags, 0, self::MAX_TAGS);
    }

    private static function specialtyTag(Doctor $doctor, ?int $cancerTypeId): string
    {
        $match = $cancerTypeId === null ? null : $doctor->cancerTypes->firstWhere('id', $cancerTypeId);

        if (! $match) {
            return 'সংশ্লিষ্ট বিশেষত্ব';
        }

        return $match->pivot->is_primary
            ? "{$match->name_bn}-এ বিশেষত্ব"
            : "{$match->name_bn}-এ অভিজ্ঞতা";
    }

    private static function districtTag(Doctor $doctor, ?int $districtId): string
    {
        $chamber = $districtId === null ? null : $doctor->chambers->firstWhere('district_id', $districtId);

        if (! $chamber || ! $chamber->district) {
            return 'সংশ্লিষ্ট জেলায় চেম্বার';
        }

        return "{$chamber->district->name_bn}-এ চেম্বার";
    }
}
