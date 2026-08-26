<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorService extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'doctor_id',
        'cancer_type_id',
        'title_bn',
        'description_bn',
        'badge_text_bn',
        'badge_color',
        'icon',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function cancerType(): BelongsTo
    {
        return $this->belongsTo(CancerType::class);
    }
}
