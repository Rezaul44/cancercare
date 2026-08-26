<?php

namespace App\Observers;

use App\Models\Doctor;
use Illuminate\Support\Str;

class DoctorObserver
{
    public function creating(Doctor $doctor): void
    {
        if (empty($doctor->slug)) {
            $doctor->slug = $this->uniqueSlug($doctor->name_en);
        }

        if (empty($doctor->rotation_seed)) {
            $doctor->rotation_seed = random_int(0, 999);
        }
    }

    public function saving(Doctor $doctor): void
    {
        if ($doctor->status === \App\Enums\DoctorStatus::Published && $doctor->doctor_approved_at === null) {
            throw new \DomainException('ডাক্তারের সম্মতি (doctor_approved_at) ছাড়া প্রোফাইল প্রকাশ করা যাবে না।');
        }
    }

    private function uniqueSlug(string $nameEn): string
    {
        $base = Str::slug($nameEn);
        $slug = $base;
        $suffix = 2;

        while (Doctor::withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
