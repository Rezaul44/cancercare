<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuideTerm extends Model
{
    protected $fillable = [
        'guide_id',
        'code',
        'slug',
        'hint_bn',
        'plain_explanation_bn',
        'why_matters_bn',
        'scale',
        'search_keywords',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'scale' => 'array',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (GuideTerm $term) {
            if (empty($term->slug)) {
                $term->slug = \Illuminate\Support\Str::slug($term->code) ?: 'term-' . uniqid();
            }
            if ($term->why_matters_bn === null) {
                $term->why_matters_bn = '';
            }
        });
    }

    public function guide(): BelongsTo
    {
        return $this->belongsTo(Guide::class);
    }
}
