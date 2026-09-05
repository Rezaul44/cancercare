<?php

namespace App\Services;

use App\Enums\HospitalCapabilityStatus;
use App\Models\Capability;
use App\Models\Hospital;
use App\Models\HospitalCapability;
use App\Models\HospitalExperienceQuestion;
use App\Models\HospitalExperienceResponse;
use App\Models\HospitalExperienceSummary;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class HospitalService
{
    /**
     * নিশ্চিত করে যে হাসপাতালের জন্য ১১টি সক্ষমতার (capabilities) প্রতিটি রেকর্ড বিদ্যমান।
     * না থাকলে 'not_available' স্ট্যাটাস দিয়ে রেকর্ড তৈরি করে।
     *
     * @return Collection<int, HospitalCapability>
     */
    public function initializeCapabilities(Hospital $hospital): Collection
    {
        $allCapabilities = Capability::orderBy('sort_order')->get();

        DB::transaction(function () use ($hospital, $allCapabilities) {
            foreach ($allCapabilities as $capability) {
                HospitalCapability::firstOrCreate(
                    [
                        'hospital_id' => $hospital->id,
                        'capability_id' => $capability->id,
                    ],
                    [
                        'status' => HospitalCapabilityStatus::NotAvailable,
                        'detail_bn' => null,
                        'machine_count' => null,
                        'last_checked_at' => now(),
                    ]
                );
            }
        });

        return $hospital->capabilities()->with('capability')->get();
    }

    /**
     * মাঠপর্যায়ে সংগৃহীত জরিপ থেকে হাসপাতালের অভিজ্ঞতা স্কোর সারসংক্ষেপ (Summary) পুনর্গণনা করে।
     * নীতি: মোট প্রতিক্রিয়া (total_count) >= ৩০ না হলে is_published = false থাকবে।
     */
    public function recalculateExperienceSummary(Hospital $hospital): void
    {
        $questions = HospitalExperienceQuestion::all();

        DB::transaction(function () use ($hospital, $questions) {
            foreach ($questions as $question) {
                $totalCount = HospitalExperienceResponse::where('hospital_id', $hospital->id)
                    ->where('question_id', $question->id)
                    ->count();

                $yesCount = HospitalExperienceResponse::where('hospital_id', $hospital->id)
                    ->where('question_id', $question->id)
                    ->where('answer', true)
                    ->count();

                $percentage = $totalCount > 0 ? round(($yesCount / $totalCount) * 100, 2) : 0.00;
                $isPublished = $totalCount >= 30;

                HospitalExperienceSummary::updateOrInsert(
                    [
                        'hospital_id' => $hospital->id,
                        'question_id' => $question->id,
                    ],
                    [
                        'yes_count' => $yesCount,
                        'total_count' => $totalCount,
                        'percentage' => $percentage,
                        'is_published' => $isPublished,
                        'last_calculated_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        });
    }
}
