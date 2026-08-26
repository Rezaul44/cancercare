<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DoctorType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'key',
        'label_bn',
        'label_en',
    ];

    public function doctors(): BelongsToMany
    {
        return $this->belongsToMany(Doctor::class, 'doctor_doctor_type');
    }
}
