<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HospitalCost extends Model
{
    protected $fillable = [
        'hospital_id',
        'service_key',
        'label_bn',
        'min_amount',
        'max_amount',
        'note_bn',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'min_amount' => 'integer',
            'max_amount' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }
}
