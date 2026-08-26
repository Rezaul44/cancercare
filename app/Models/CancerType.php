<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CancerType extends Model
{
    protected $fillable = [
        'name_bn',
        'name_en',
        'slug',
        'icon',
        'color_key',
        'short_description_bn',
        'gender_bias',
        'is_common',
        'doctor_count_cache',
        'hospital_count_cache',
        'guide_published',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_common' => 'boolean',
            'guide_published' => 'boolean',
            'doctor_count_cache' => 'integer',
            'hospital_count_cache' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget('home_cancer_types');
            \Illuminate\Support\Facades\Cache::forget('guide_index_published_cancer_types');
            \Illuminate\Support\Facades\Cache::forget('total_cancer_types_count');
            \Illuminate\Support\Facades\Cache::forget('match_cancer_options');
        });

        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::forget('home_cancer_types');
            \Illuminate\Support\Facades\Cache::forget('guide_index_published_cancer_types');
            \Illuminate\Support\Facades\Cache::forget('total_cancer_types_count');
            \Illuminate\Support\Facades\Cache::forget('match_cancer_options');
        });
    }

    public function doctors(): BelongsToMany
    {
        return $this->belongsToMany(Doctor::class, 'doctor_cancer_type')->withPivot('is_primary');
    }

    public function services(): HasMany
    {
        return $this->hasMany(DoctorService::class);
    }

    public function guide(): HasOne
    {
        return $this->hasOne(Guide::class);
    }
}
