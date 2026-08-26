<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorCancerTypeStage extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'doctor_id',
        'cancer_type_id',
        'stage',
        'case_count',
        'success_rate_percent',
        'note_bn',
    ];

    protected function casts(): array
    {
        return [
            'case_count' => 'integer',
            'success_rate_percent' => 'integer',
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
}
