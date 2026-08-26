<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuideStage extends Model
{
    protected $fillable = [
        'guide_id',
        'stage',
        'title_bn',
        'description_bn',
        'typical_treatment_bn',
        'duration_bn',
        'cost_min',
        'cost_max',
        'severity_color',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'cost_min' => 'integer',
            'cost_max' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function guide(): BelongsTo
    {
        return $this->belongsTo(Guide::class);
    }
}
