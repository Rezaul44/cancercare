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

    public function getWatchUrlAttribute(): string
    {
        return \App\Support\YouTubeHelper::toWatchUrl($this->video_url) ?? (string) $this->video_url;
    }

    public function getEmbedUrlAttribute(): string
    {
        return \App\Support\YouTubeHelper::toEmbedUrl($this->video_url) ?? (string) $this->video_url;
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if ($this->thumbnail_path) {
            return \Illuminate\Support\Facades\Storage::disk('public')->url($this->thumbnail_path);
        }

        return \App\Support\YouTubeHelper::getThumbnailUrl($this->video_url);
    }
}
