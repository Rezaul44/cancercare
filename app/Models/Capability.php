<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Capability extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'key',
        'label_bn',
        'icon',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function hospitalCapabilities(): HasMany
    {
        return $this->hasMany(HospitalCapability::class);
    }
}
