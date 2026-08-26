<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorPatientTestimonial extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'doctor_id',
        'cancer_type_id',
        'stage',
        'outcome_bn',
        'anonymized_label_bn',
        'year',
        'video_url',
        'duration_seconds',
        'thumbnail_color_key',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'duration_seconds' => 'integer',
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
}
