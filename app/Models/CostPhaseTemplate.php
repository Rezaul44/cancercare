<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CostPhaseTemplate extends Model
{
    protected $fillable = [
        'cancer_type_id',
        'service_key',
        'phase_title_bn',
        'when_bn',
        'breakdown',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'breakdown' => 'array',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => \Illuminate\Support\Facades\Cache::forget('cost_estimator_rates'));
        static::deleted(fn () => \Illuminate\Support\Facades\Cache::forget('cost_estimator_rates'));
    }

    public function cancerType(): BelongsTo
    {
        return $this->belongsTo(CancerType::class);
    }
}
