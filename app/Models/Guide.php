<?php

namespace App\Models;

use App\Enums\GuideStatus;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guide extends Model
{
    protected $fillable = [
        'cancer_type_id',
        'title_bn',
        'intro_bn',
        'meta_title',
        'meta_description',
        'reviewed_by_doctor_id',
        'reviewed_at',
        'sources_note_bn',
        'read_minutes',
        'status',
        'published_at',
        'last_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => GuideStatus::class,
            'read_minutes' => 'integer',
            'reviewed_at' => 'date',
            'published_at' => 'datetime',
            'last_updated_at' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Guide $guide) {
            $isPublished = $guide->status === GuideStatus::Published
                || $guide->status === GuideStatus::Published->value;

            if ($isPublished && $guide->reviewed_by_doctor_id === null) {
                throw new DomainException('ডাক্তার কর্তৃক পর্যালোচিত (reviewed_by_doctor_id) না হলে গাইড প্রকাশ করা যাবে না।');
            }
        });

        static::saved(function (Guide $guide) {
            \Illuminate\Support\Facades\Cache::forget('guide_index_published_cancer_types');
            \Illuminate\Support\Facades\Cache::forget('home_cancer_types');
            \Illuminate\Support\Facades\Cache::forget('total_cancer_types_count');
            \Illuminate\Support\Facades\Cache::forget('guide_other_types_'.$guide->cancer_type_id);
        });

        static::deleted(function (Guide $guide) {
            \Illuminate\Support\Facades\Cache::forget('guide_index_published_cancer_types');
            \Illuminate\Support\Facades\Cache::forget('home_cancer_types');
            \Illuminate\Support\Facades\Cache::forget('total_cancer_types_count');
            \Illuminate\Support\Facades\Cache::forget('guide_other_types_'.$guide->cancer_type_id);
        });
    }

    public function cancerType(): BelongsTo
    {
        return $this->belongsTo(CancerType::class);
    }

    public function reviewedByDoctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'reviewed_by_doctor_id');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(GuideVideo::class)->orderBy('sort_order');
    }

    public function terms(): HasMany
    {
        return $this->hasMany(GuideTerm::class)->orderBy('sort_order');
    }

    public function stages(): HasMany
    {
        return $this->hasMany(GuideStage::class)->orderBy('sort_order');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(GuideStep::class)->orderBy('sort_order');
    }

    public function myths(): HasMany
    {
        return $this->hasMany(GuideMyth::class)->orderBy('sort_order');
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(GuideFaq::class)->orderBy('sort_order');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', GuideStatus::Published);
    }
}
