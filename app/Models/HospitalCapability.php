<?php

namespace App\Models;

use App\Enums\HospitalCapabilityStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HospitalCapability extends Model
{
    protected $fillable = [
        'hospital_id',
        'capability_id',
        'status',
        'detail_bn',
        'machine_count',
        'last_checked_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => HospitalCapabilityStatus::class,
            'machine_count' => 'integer',
            'last_checked_at' => 'date',
        ];
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function capability(): BelongsTo
    {
        return $this->belongsTo(Capability::class);
    }
}
