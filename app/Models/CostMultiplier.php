<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CostMultiplier extends Model
{
    protected $fillable = [
        'group',
        'key',
        'multiplier',
        'label_bn',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'multiplier' => 'float',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => \Illuminate\Support\Facades\Cache::forget('cost_estimator_rates'));
        static::deleted(fn () => \Illuminate\Support\Facades\Cache::forget('cost_estimator_rates'));
    }
}
