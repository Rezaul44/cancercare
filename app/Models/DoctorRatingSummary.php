<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorRatingSummary extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'doctor_id';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'doctor_id',
        'total_count',
        'criteria_scores',
        'overall_score',
        'is_published',
        'last_calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'total_count' => 'integer',
            'criteria_scores' => 'array',
            'overall_score' => 'decimal:1',
            'is_published' => 'boolean',
            'last_calculated_at' => 'datetime',
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
