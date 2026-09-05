<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientCaseCost extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_case_id',
        'item_bn',
        'amount',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function patientCase(): BelongsTo
    {
        return $this->belongsTo(PatientCase::class);
    }
}
