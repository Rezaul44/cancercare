<?php

namespace App\Services;

use App\Models\CancerType;
use App\Models\Doctor;

/**
 * "এই ডাক্তার কি আমার জন্য" ম্যাচ ইঞ্জিন সার্ভিস — docs/CCB_prompt_playbook.md ধারা ২.৭
 *
 * doctor_cancer_type, doctor_cancer_type_stages, doctor_treatment_specialties এবং doctor_services
 * টেবিল দেখে verdict নির্ধারণ করে:
 * - ক্যান্সার type মিলে না -> "উপযুক্ত নন" (ok: 0) + directory redirect link
 * - মিলে কিন্তু treatment আংশিক/রেফারেল -> "ভালো মিল, কিছু বিবেচনা" (ok: 1, verdict: partial) + অন্য বিশেষজ্ঞের পরামর্শ
 * - সব মিলে -> "উপযুক্ত" (ok: 1, verdict: suitable)
 * - 'other' বা অস্পষ্ট -> "নিশ্চিত করা যাচ্ছে না" (ok: 2)
 */
class DoctorMatchService
{
    /**
     * @var list<string>
     */
    public const MATCH_STAGES = ['1', '2', '3', '4', 'unknown'];

    /**
     * @var list<string>
     */
    public const MATCH_TREATMENTS = ['surgery', 'chemo', 'radiation', 'hormone', 'unknown'];

    /**
     * @var list<string>
     */
    public const MATCH_CANCER_SLUGS = [
        'breast-cancer', 'cervical-cancer', 'ovarian-cancer', 'lung-cancer', 'blood-cancer', 'stomach-cancer',
    ];

    /**
     * @return array{
     *     ok: int|null,
     *     verdict: string|null,
     *     stage: array{case_count: int, success_rate_percent: int|null, note_bn: string}|null,
     *     treatment: array{role: string, note_bn: string}|null,
     *     redirect_url: string|null,
     *     message: string|null,
     * }
     */
    public function match(Doctor $doctor, ?string $cancerSlug, ?string $stage, ?string $treatment): array
    {
        $cancerSlug = (string) $cancerSlug;

        if ($cancerSlug === '') {
            return [
                'ok' => null,
                'verdict' => null,
                'stage' => null,
                'treatment' => null,
                'redirect_url' => null,
                'message' => 'ক্যান্সারের ধরন বেছে নিন',
            ];
        }

        if ($cancerSlug === 'other' || ! in_array($cancerSlug, self::MATCH_CANCER_SLUGS, true)) {
            return [
                'ok' => 2,
                'verdict' => 'uncertain',
                'stage' => null,
                'treatment' => null,
                'redirect_url' => route('doctors.index'),
                'message' => 'নিশ্চিত করা যাচ্ছে না। ক্যান্সারের ধরন নির্দিষ্ট করে জানালে সঠিক ফলাফল বলা সম্ভব হবে।',
            ];
        }

        $cancerType = CancerType::where('slug', $cancerSlug)->first();

        if (! $cancerType || ! $doctor->cancerTypes()->where('cancer_types.id', $cancerType->id)->exists()) {
            return [
                'ok' => 0,
                'verdict' => 'not_a_match',
                'stage' => null,
                'treatment' => null,
                'redirect_url' => route('doctors.index', ['cancer' => $cancerSlug]),
                'message' => 'এই ডাক্তার আপনার জন্য উপযুক্ত নন। সঠিক বিশেষজ্ঞ দেখানোই নিরাপদ।',
            ];
        }

        $stageData = $this->stageData($doctor, $cancerType->id, (string) $stage);
        $treatmentData = $this->treatmentData($doctor, $cancerType->id, (string) $treatment);

        $isPartial = ($stage === '4') || ($treatmentData && $treatmentData['role'] === 'refers');
        $verdict = $isPartial ? 'partial' : 'suitable';

        return [
            'ok' => 1,
            'verdict' => $verdict,
            'stage' => $stageData,
            'treatment' => $treatmentData,
            'redirect_url' => null,
            'message' => $isPartial
                ? 'ভালো মিল — কিছু বিষয় বিবেচনা করুন'
                : 'ভালো মিল — এই ডাক্তার আপনার জন্য উপযুক্ত',
        ];
    }

    /**
     * @return array{case_count: int, success_rate_percent: int|null, note_bn: string}|null
     */
    private function stageData(Doctor $doctor, int $cancerTypeId, string $stage): ?array
    {
        if (! in_array($stage, self::MATCH_STAGES, true)) {
            return null;
        }

        $row = $doctor->cancerTypeStages()
            ->where('cancer_type_id', $cancerTypeId)
            ->where('stage', $stage)
            ->first()
            ?? $doctor->cancerTypeStages()->where('cancer_type_id', $cancerTypeId)->where('stage', 'unknown')->first();

        if (! $row) {
            return null;
        }

        return [
            'case_count' => (int) $row->case_count,
            'success_rate_percent' => $row->success_rate_percent !== null ? (int) $row->success_rate_percent : null,
            'note_bn' => (string) $row->note_bn,
        ];
    }

    /**
     * @return array{role: string, note_bn: string}|null
     */
    private function treatmentData(Doctor $doctor, int $cancerTypeId, string $treatment): ?array
    {
        if (! in_array($treatment, self::MATCH_TREATMENTS, true)) {
            return null;
        }

        $row = $doctor->treatmentSpecialties()
            ->where('cancer_type_id', $cancerTypeId)
            ->where('treatment_key', $treatment)
            ->first()
            ?? $doctor->treatmentSpecialties()->where('cancer_type_id', $cancerTypeId)->where('treatment_key', 'unknown')->first();

        if (! $row) {
            return null;
        }

        return [
            'role' => (string) $row->role,
            'note_bn' => (string) $row->note_bn,
        ];
    }
}
