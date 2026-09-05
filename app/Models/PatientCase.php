<?php

namespace App\Models;

use App\Enums\PatientCaseAnonymity;
use App\Enums\PatientCaseGender;
use App\Enums\PatientCaseStatus;
use App\Observers\PatientCaseObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

#[ObservedBy(PatientCaseObserver::class)]
class PatientCase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'case_code',
        'real_name',
        'display_name_bn',
        'age',
        'gender',
        'cancer_type_id',
        'stage',
        'district_id',
        'hospital_id',
        'treating_doctor_name',
        'story_bn',
        'amount_needed',
        'photo_path',
        'show_photo',
        'anonymity_level',
        'consent_form_path',
        'consent_signed_at',
        'status',
        'verified_at',
        'published_at',
        'expires_at',
        'created_by',
        'verified_by',
    ];

    protected function casts(): array
    {
        return [
            'age' => 'integer',
            'amount_needed' => 'integer',
            'show_photo' => 'boolean',
            'gender' => PatientCaseGender::class,
            'anonymity_level' => PatientCaseAnonymity::class,
            'status' => PatientCaseStatus::class,
            'consent_signed_at' => 'date',
            'verified_at' => 'datetime',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function cancerType(): BelongsTo
    {
        return $this->belongsTo(CancerType::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(PatientCaseVerification::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(PatientCaseDocument::class);
    }

    public function costs(): HasMany
    {
        return $this->hasMany(PatientCaseCost::class)->orderBy('sort_order');
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(PatientCaseAccount::class);
    }

    public function updates(): HasMany
    {
        return $this->hasMany(PatientCaseUpdate::class)->orderByDesc('update_date');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(PatientCaseReport::class);
    }

    /**
     * প্রকাশিত ও এখনো মেয়াদ-উত্তীর্ণ হয়নি এমন কেস — PatientCaseController-এর আগের ইনলাইন শর্তটাই এখানে।
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PatientCaseStatus::Published)
            ->where(function (Builder $q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', Carbon::now());
            });
    }
}
