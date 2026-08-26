<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorTreatmentSpecialty extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'doctor_id',
        'cancer_type_id',
        'treatment_key',
        'role',
        'note_bn',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function cancerType(): BelongsTo
    {
        return $this->belongsTo(CancerType::class);
    }
}
