<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuideFaq extends Model
{
    protected $fillable = [
        'guide_id',
        'question_bn',
        'answer_bn',
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
