<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CostBaseRate extends Model
{
    protected $fillable = [
        'cancer_type_id',
        'service_key',
        'govt_amount',
        'default_months',
        'is_applicable',
    ];

    protected function casts(): array
    {
        return [
            'govt_amount' => 'integer',
            'default_months' => 'integer',
            'is_applicable' => 'boolean',
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
