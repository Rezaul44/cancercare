<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorPatientStory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'doctor_id',
        'cancer_type_id',
        'stage',
        'district_id',
        'patient_label_bn',
        'year',
        'outcome_duration_bn',
        'quote_bn',
        'then_bn',
        'now_bn',
        'is_name_changed',
        'is_family_told',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'is_name_changed' => 'boolean',
            'is_family_told' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function cancerType(): BelongsTo
    {
        return $this->belongsTo(CancerType::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }
}
