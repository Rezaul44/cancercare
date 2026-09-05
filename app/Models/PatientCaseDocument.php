<?php

namespace App\Models;

use App\Enums\PatientCaseDocumentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientCaseDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_case_id',
        'type',
        'file_path',
        'is_public',
        'redacted',
        'uploaded_by',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => PatientCaseDocumentType::class,
            'is_public' => 'boolean',
            'redacted' => 'boolean',
            'uploaded_at' => 'datetime',
        ];
    }

    public function patientCase(): BelongsTo
    {
        return $this->belongsTo(PatientCase::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
