<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuideMyth extends Model
{
    protected $fillable = [
        'guide_id',
        'myth_bn',
        'truth_bn',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function guide(): BelongsTo
    {
        return $this->belongsTo(Guide::class);
    }
}
