<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorTimeline extends Model
{
    public $timestamps = false;

    protected $table = 'doctor_timeline';

    protected $fillable = [
        'doctor_id',
        'year_label',
        'title_bn',
        'institution_bn',
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
}
