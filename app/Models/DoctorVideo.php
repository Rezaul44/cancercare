<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorVideo extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'doctor_id',
        'type',
        'platform',
        'video_url',
        'thumbnail_path',
        'title_bn',
        'description_bn',
        'duration_seconds',
        'view_count',
        'produced_by',
        'is_paid_production',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'duration_seconds' => 'integer',
            'view_count' => 'integer',
            'is_paid_production' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
