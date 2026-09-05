<?php

namespace App\Models;

use App\Enums\ChamberType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chamber extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'doctor_id',
        'hospital_id',
        'name_bn',
        'address_bn',
        'district_id',
        'type',
        'fee',
        'days_bn',
        'time_from',
        'time_to',
        'avg_wait_minutes',
        'next_available_note',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'type' => ChamberType::class,
            'fee' => 'integer',
            'avg_wait_minutes' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }
}
