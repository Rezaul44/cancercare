<?php

namespace App\Models;

use App\Enums\HospitalStatus;
use App\Enums\HospitalType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hospital extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name_bn',
        'name_en',
        'slug',
        'type',
        'district_id',
        'address_bn',
        'latitude',
        'longitude',
        'phone',
        'established_year',
        'bed_count',
        'oncologist_count',
        'outdoor_fee',
        'emergency_24h',
        'annual_patients',
        'cover_photo_path',
        'description_bn',
        'last_verified_at',
        'verified_by',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'type' => HospitalType::class,
            'status' => HospitalStatus::class,
            'district_id' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'established_year' => 'integer',
            'bed_count' => 'integer',
            'oncologist_count' => 'integer',
            'outdoor_fee' => 'integer',
            'emergency_24h' => 'boolean',
            'last_verified_at' => 'date',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', HospitalStatus::Published);
    }

    public function scopeGovt(Builder $query): Builder
    {
        return $query->where('type', HospitalType::Govt);
    }

    public function scopePrivate(Builder $query): Builder
    {
        return $query->where('type', HospitalType::Private);
    }

    public function scopeNpo(Builder $query): Builder
    {
        return $query->where('type', HospitalType::Npo);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function verifiedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function capabilities(): HasMany
    {
        return $this->hasMany(HospitalCapability::class);
    }

    public function waitTimes(): HasMany
    {
        return $this->hasMany(HospitalWaitTime::class);
    }

    public function costs(): HasMany
    {
        return $this->hasMany(HospitalCost::class);
    }

    public function prepInfos(): HasMany
    {
        return $this->hasMany(HospitalPrepInfo::class);
    }

    public function practicalInfos(): HasMany
    {
        return $this->hasMany(HospitalPracticalInfo::class);
    }

    public function videos(): HasMany
    {
        return $this->hasMany(HospitalVideo::class);
    }

    public function experienceResponses(): HasMany
    {
        return $this->hasMany(HospitalExperienceResponse::class);
    }

    public function experienceSummaries(): HasMany
    {
        return $this->hasMany(HospitalExperienceSummary::class);
    }

    public function doctors(): BelongsToMany
    {
        return $this->belongsToMany(Doctor::class, 'hospital_doctor')
            ->withPivot('schedule_note_bn', 'sort_order')
            ->withTimestamps();
    }

    public function chambers(): HasMany
    {
        return $this->hasMany(Chamber::class);
    }

    public function patientCases(): HasMany
    {
        return $this->hasMany(PatientCase::class);
    }
}
