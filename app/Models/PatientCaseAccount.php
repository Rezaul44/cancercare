<?php

namespace App\Models;

use App\Enums\PatientCaseAccountType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientCaseAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_case_id',
        'type',
        'account_number',
        'account_name',
        'bank_name',
        'branch',
        'name_verified',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => PatientCaseAccountType::class,
            'name_verified' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function patientCase(): BelongsTo
    {
        return $this->belongsTo(PatientCase::class);
    }
}
