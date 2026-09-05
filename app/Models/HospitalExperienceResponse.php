<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HospitalExperienceResponse extends Model
{
    protected $fillable = [
        'hospital_id',
        'question_id',
        'answer',
        'collected_by',
        'source',
        'collected_at',
    ];

    protected function casts(): array
    {
        return [
            'answer' => 'boolean',
            'collected_at' => 'date',
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

    public function collectedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }
}
