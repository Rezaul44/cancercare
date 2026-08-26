<?php

namespace App\Services;

use App\Enums\GuideStatus;
use App\Models\Doctor;
use App\Models\Guide;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GuideService
{
    private function clearGuideCaches(): void
    {
        Cache::forget('guide_index_published_cancer_types');
        Cache::forget('home_cancer_types');
        Cache::forget('total_cancer_types_count');
        Cache::forget('total_published_doctors_count');
    }
    /**
     * মেডিকেল রিভিউয়ার কর্তৃক চিকিৎসা তথ্য অনুমোদন।
     */
    public function medicalApprove(Guide $guide, int $doctorId, ?string $note = null, ?User $causer = null): Guide
    {
        $doctor = Doctor::find($doctorId);
        if (! $doctor) {
            throw new DomainException('নির্বাচিত ডাক্তার পাওয়া যায়নি।');
        }

        return DB::transaction(function () use ($guide, $doctor, $note, $causer) {
            $guide->reviewed_by_doctor_id = $doctor->id;
            $guide->reviewed_at = now();

            // যদি খসড়া অবস্থায় থাকে, তবে পর্যালোচনায় (in_review) স্ট্যাটাসে নেবে
            if ($guide->status === GuideStatus::Draft) {
                $guide->status = GuideStatus::InReview;
            }

            $guide->save();

            activity()
                ->performedOn($guide)
                ->causedBy($causer)
                ->withProperties([
                    'doctor_id' => $doctor->id,
                    'doctor_name' => $doctor->name_bn,
                    'note' => $note,
                ])
                ->log('guide.medical_approved');

            $this->clearGuideCaches();

            return $guide;
        });
    }

    /**
     * সুপার অ্যাডমিন কর্তৃক গাইড প্রকাশ।
     * শর্ত: ডাক্তার কর্তৃক পর্যালোচিত (reviewed_by_doctor_id ও reviewed_at) থাকতে হবে।
     */
    public function publish(Guide $guide, ?User $causer = null): Guide
    {
        if (empty($guide->reviewed_by_doctor_id) || empty($guide->reviewed_at)) {
            throw new DomainException('ডাক্তার কর্তৃক পর্যালোচিত না হলে গাইড প্রকাশ করা যাবে না।');
        }

        return DB::transaction(function () use ($guide, $causer) {
            $guide->status = GuideStatus::Published;
            $guide->published_at = $guide->published_at ?? now();
            $guide->last_updated_at = now();
            $guide->save();

            if ($guide->cancerType) {
                $guide->cancerType->update(['guide_published' => true]);
            }

            activity()
                ->performedOn($guide)
                ->causedBy($causer)
                ->log('guide.published');

            $this->clearGuideCaches();

            return $guide;
        });
    }

    /**
     * সুপার অ্যাডমিন কর্তৃক গাইড অপ্রকাশিত / খসড়া করা।
     */
    public function unpublish(Guide $guide, ?User $causer = null): Guide
    {
        return DB::transaction(function () use ($guide, $causer) {
            $guide->status = GuideStatus::Draft;
            $guide->save();

            if ($guide->cancerType) {
                $guide->cancerType->update(['guide_published' => false]);
            }

            activity()
                ->performedOn($guide)
                ->causedBy($causer)
                ->log('guide.unpublished');

            $this->clearGuideCaches();

            return $guide;
        });
    }
}
