<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientCaseUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_case_id',
        'note_bn',
        'update_date',
        'created_by',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'update_date' => 'date',
            'is_public' => 'boolean',
        ];
    }

    public function patientCase(): BelongsTo
    {
        return $this->belongsTo(PatientCase::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
