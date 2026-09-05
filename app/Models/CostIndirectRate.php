<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CostIndirectRate extends Model
{
    protected $fillable = [
        'key',
        'label_bn',
        'base_amount',
        'unit',
        'percent_value',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'base_amount' => 'integer',
            'percent_value' => 'float',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => \Illuminate\Support\Facades\Cache::forget('cost_estimator_rates'));
        static::deleted(fn () => \Illuminate\Support\Facades\Cache::forget('cost_estimator_rates'));
    }
}
