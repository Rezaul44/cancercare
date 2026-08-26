<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorApplication extends Model
{
    protected $fillable = [
        'full_name',
        'bmdc_number',
        'phone',
        'email',
        'photo_path',
        'bmdc_certificate_path',
        'experience_years',
        'current_position',
        'degrees',
        'timeline',
        'doctor_type_ids',
        'cancer_type_ids',
        'chambers',
        'extra_services',
        'preferred_call_time',
        'preferred_call_day',
        'declarations',
        'status',
        'reviewed_by',
        'review_note',
        'verification_checklist',
        'doctor_id',
    ];

    protected function casts(): array
    {
        return [
            'experience_years' => 'integer',
            'degrees' => 'array',
            'timeline' => 'array',
            'doctor_type_ids' => 'array',
            'cancer_type_ids' => 'array',
            'chambers' => 'array',
            'extra_services' => 'array',
            'declarations' => 'array',
            'verification_checklist' => 'array',
        ];
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function isVerified(): bool
    {
        return (bool) ($this->verification_checklist['bmdc_verified'] ?? false)
            && (bool) ($this->verification_checklist['degree_verified'] ?? false);
    }
}
