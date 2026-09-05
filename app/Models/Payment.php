<?php

namespace App\Models;

use App\Enums\PaymentGateway;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Payment extends Model
{
    protected $fillable = [
        'payable_type',
        'payable_id',
        'gateway',
        'amount',
        'gateway_fee',
        'transaction_id',
        'status',
        'raw_response',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'gateway' => PaymentGateway::class,
            'amount' => 'integer',
            'gateway_fee' => 'integer',
            'status' => PaymentStatus::class,
            'raw_response' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }
}
