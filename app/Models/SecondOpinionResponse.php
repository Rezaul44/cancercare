<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecondOpinionResponse extends Model
{
    protected $fillable = [
        'request_id',
        'doctor_id',
        'response_bn',
        'call_made',
        'call_note',
        'answered_at',
    ];

    protected function casts(): array
    {
        return [
            'call_made' => 'boolean',
            'answered_at' => 'datetime',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(SecondOpinionRequest::class, 'request_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
