<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorRatingSubmission extends Model
{
    protected $fillable = [
        'doctor_id',
        'collected_by',
        'source',
        'collection_location',
        'patient_phone_hash',
        'proof_type',
        'proof_path',
        'answers',
        'free_comment_bn',
        'is_verified',
        'verified_by',
        'collected_at',
    ];

    protected function casts(): array
    {
        return [
            'answers' => 'array',
            'is_verified' => 'boolean',
            'collected_at' => 'date',
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function collectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
