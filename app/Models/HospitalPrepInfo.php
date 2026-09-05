<?php

namespace App\Models;

use App\Enums\HospitalFlagType;
use App\Enums\HospitalPrepKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HospitalPrepInfo extends Model
{
    protected $table = 'hospital_prep_info';

    protected $fillable = [
        'hospital_id',
        'key',
        'title_bn',
        'description_bn',
        'flag_text_bn',
        'flag_type',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'key' => HospitalPrepKey::class,
            'flag_type' => HospitalFlagType::class,
            'sort_order' => 'integer',
        ];
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }
}
