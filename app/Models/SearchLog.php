<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'query',
        'results_count',
        'clicked_type',
        'clicked_id',
        'session_hash',
    ];

    protected function casts(): array
    {
        return [
            'results_count' => 'integer',
        ];
    }
}
