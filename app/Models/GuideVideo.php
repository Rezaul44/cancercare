<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuideVideo extends Model
{
    protected $fillable = [
        'guide_id',
        'video_url',
        'platform',
        'title_bn',
        'description_bn',
        'duration_seconds',
        'doctor_id',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'duration_seconds' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function guide(): BelongsTo
    {
        return $this->belongsTo(Guide::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
