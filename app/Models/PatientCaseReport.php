<?php

namespace App\Models;

use App\Enums\PatientCaseReportStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientCaseReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_case_id',
        'reporter_phone',
        'reason',
        'details',
        'status',
        'handled_by',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PatientCaseReportStatus::class,
            'resolved_at' => 'datetime',
        ];
    }

    public function patientCase(): BelongsTo
    {
        return $this->belongsTo(PatientCase::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
