<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HospitalExperienceSummary extends Model
{
    protected $fillable = [
        'hospital_id',
        'question_id',
        'yes_count',
        'total_count',
        'percentage',
        'is_published',
        'last_calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'yes_count' => 'integer',
            'total_count' => 'integer',
            'percentage' => 'decimal:2',
            'is_published' => 'boolean',
            'last_calculated_at' => 'datetime',
        ];
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(HospitalExperienceQuestion::class, 'question_id');
    }
}
