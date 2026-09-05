<?php

namespace App\Models;

use App\Enums\SecondOpinionCurrentStatus;
use App\Enums\SecondOpinionStatus;
use App\Observers\SecondOpinionRequestObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[ObservedBy(SecondOpinionRequestObserver::class)]
class SecondOpinionRequest extends Model
{
    protected $fillable = [
        'request_code',
        'patient_name',
        'age',
        'cancer_type_id',
        'current_status',
        'treatments_done_bn',
        'question_bn',
        'phone',
        'district_id',
        'doctor_id',
        'fee',
        'payment_id',
        'status',
        'expected_hours',
        'answered_at',
    ];

    protected function casts(): array
    {
        return [
            'age' => 'integer',
            'current_status' => SecondOpinionCurrentStatus::class,
            'fee' => 'integer',
            'status' => SecondOpinionStatus::class,
            'expected_hours' => 'integer',
            'answered_at' => 'datetime',
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

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function paymentAttempts(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function files(): HasMany
    {
        return $this->hasMany(SecondOpinionFile::class, 'request_id');
    }

    public function response(): HasOne
    {
        return $this->hasOne(SecondOpinionResponse::class, 'request_id');
    }
}
