<?php

namespace App\Models;

use App\Enums\HospitalWaitTimeSeverity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HospitalWaitTime extends Model
{
    protected $fillable = [
        'hospital_id',
        'service_key',
        'min_weeks',
        'max_weeks',
        'label_bn',
        'severity',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'severity' => HospitalWaitTimeSeverity::class,
            'min_weeks' => 'integer',
            'max_weeks' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }
}
