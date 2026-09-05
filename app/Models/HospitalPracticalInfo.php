<?php

namespace App\Models;

use App\Enums\HospitalPracticalKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HospitalPracticalInfo extends Model
{
    protected $table = 'hospital_practical_info';

    protected $fillable = [
        'hospital_id',
        'key',
        'title_bn',
        'description_bn',
        'icon',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'key' => HospitalPracticalKey::class,
            'sort_order' => 'integer',
        ];
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }
}
