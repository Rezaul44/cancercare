<?php

namespace App\Models;

use App\Enums\DoctorPayoutStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorPayout extends Model
{
    protected $fillable = [
        'doctor_id',
        'period_start',
        'period_end',
        'request_count',
        'gross_amount',
        'gateway_fee',
        'net_amount',
        'status',
        'paid_at',
        'reference',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'request_count' => 'integer',
            'gross_amount' => 'integer',
            'gateway_fee' => 'integer',
            'net_amount' => 'integer',
            'status' => DoctorPayoutStatus::class,
            'paid_at' => 'datetime',
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
