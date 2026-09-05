<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecondOpinionFile extends Model
{
    protected $fillable = [
        'request_id',
        'file_path',
        'original_name',
        'mime',
        'size_bytes',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'uploaded_at' => 'datetime',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(SecondOpinionRequest::class, 'request_id');
    }
}
