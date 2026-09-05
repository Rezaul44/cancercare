<?php

namespace App\Models;

use App\Enums\PatientCaseVerificationStatus;
use App\Enums\PatientCaseVerificationStep;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientCaseVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_case_id',
        'step',
        'status',
        'note_bn',
        'completed_by',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'step' => PatientCaseVerificationStep::class,
            'status' => PatientCaseVerificationStatus::class,
            'completed_at' => 'date',
        ];
    }

    public function patientCase(): BelongsTo
    {
        return $this->belongsTo(PatientCase::class);
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
