<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HospitalVideo extends Model
{
    protected $fillable = [
        'hospital_id',
        'video_url',
        'platform',
        'title_bn',
        'description_bn',
        'duration_seconds',
        'produced_by',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'duration_seconds' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }
}
