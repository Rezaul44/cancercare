<?php

namespace App\Models;

use App\Enums\DoctorStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Doctor extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'application_id',
        'name_bn',
        'name_en',
        'slug',
        'bmdc_number',
        'bmdc_verified_at',
        'bmdc_verified_by',
        'photo_path',
        'degrees_line_bn',
        'experience_years',
        'current_position_bn',
        'gender',
        'philosophy_intro_bn',
        'patients_treated',
        'offers_second_opinion',
        'offers_whatsapp',
        'whatsapp_fee',
        'whatsapp_response_hours',
        'second_opinion_fee',
        'status',
        'doctor_approved_at',
        'published_at',
        'last_verified_at',
        'rotation_seed',
    ];

    protected function casts(): array
    {
        return [
            'status' => DoctorStatus::class,
            'bmdc_verified_at' => 'datetime',
            'doctor_approved_at' => 'datetime',
            'published_at' => 'datetime',
            'last_verified_at' => 'date',
            'offers_second_opinion' => 'boolean',
            'offers_whatsapp' => 'boolean',
            'experience_years' => 'integer',
            'patients_treated' => 'integer',
            'whatsapp_fee' => 'integer',
            'second_opinion_fee' => 'integer',
            'rotation_seed' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget('total_published_doctors_count');
        });

        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::forget('total_published_doctors_count');
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(DoctorApplication::class, 'application_id');
    }

    public function bmdcVerifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bmdc_verified_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(DoctorDocument::class);
    }

    public function timeline(): HasMany
    {
        return $this->hasMany(DoctorTimeline::class);
    }

    public function doctorTypes(): BelongsToMany
    {
        return $this->belongsToMany(DoctorType::class, 'doctor_doctor_type');
    }

    public function cancerTypes(): BelongsToMany
    {
        return $this->belongsToMany(CancerType::class, 'doctor_cancer_type')->withPivot('is_primary');
    }

    public function services(): HasMany
    {
        return $this->hasMany(DoctorService::class);
    }

    public function philosophyPoints(): HasMany
    {
        return $this->hasMany(DoctorPhilosophyPoint::class);
    }

    public function videos(): HasMany
    {
        return $this->hasMany(DoctorVideo::class);
    }

    public function chambers(): HasMany
    {
        return $this->hasMany(Chamber::class);
    }

    public function cancerTypeStages(): HasMany
    {
        return $this->hasMany(DoctorCancerTypeStage::class);
    }

    public function treatmentSpecialties(): HasMany
    {
        return $this->hasMany(DoctorTreatmentSpecialty::class);
    }

    public function patientTestimonials(): HasMany
    {
        return $this->hasMany(DoctorPatientTestimonial::class);
    }

    public function patientStories(): HasMany
    {
        return $this->hasMany(DoctorPatientStory::class);
    }

    public function storyHighlights(): HasMany
    {
        return $this->hasMany(DoctorStoryHighlight::class);
    }

    public function ratingSubmissions(): HasMany
    {
        return $this->hasMany(DoctorRatingSubmission::class);
    }

    public function ratingSummary(): HasOne
    {
        return $this->hasOne(DoctorRatingSummary::class);
    }

    public function reviewedGuides(): HasMany
    {
        return $this->hasMany(Guide::class, 'reviewed_by_doctor_id');
    }

    public function guideVideos(): HasMany
    {
        return $this->hasMany(GuideVideo::class);
    }

    public function hospitals(): BelongsToMany
    {
        return $this->belongsToMany(Hospital::class, 'hospital_doctor')
            ->withPivot('schedule_note_bn', 'sort_order')
            ->withTimestamps();
    }

    public function secondOpinionRequests(): HasMany
    {
        return $this->hasMany(SecondOpinionRequest::class);
    }

    public function secondOpinionResponses(): HasMany
    {
        return $this->hasMany(SecondOpinionResponse::class);
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(DoctorPayout::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', DoctorStatus::Published);
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->whereNotNull('bmdc_verified_at');
    }
}
