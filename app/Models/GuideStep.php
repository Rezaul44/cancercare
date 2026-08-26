<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuideStep extends Model
{
    protected $fillable = [
        'guide_id',
        'step_no',
        'title_bn',
        'description_bn',
        'when_label_bn',
        'urgency',
        'items',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'step_no' => 'integer',
            'items' => 'array',
            'sort_order' => 'integer',
        ];
    }

    public function guide(): BelongsTo
    {
        return $this->belongsTo(Guide::class);
    }
}
