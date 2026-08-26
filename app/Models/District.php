<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'division_id',
        'name_bn',
        'name_en',
        'slug',
        'distance_tier',
        'has_cancer_center',
    ];

    protected function casts(): array
    {
        return [
            'has_cancer_center' => 'boolean',
        ];
    }

    public function chambers(): HasMany
    {
        return $this->hasMany(Chamber::class);
    }
}
