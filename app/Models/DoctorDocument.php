<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorDocument extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'doctor_id',
        'type',
        'file_path',
        'uploaded_at',
        'is_private',
    ];

    protected function casts(): array
    {
        return [
            'uploaded_at' => 'datetime',
            'is_private' => 'boolean',
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
