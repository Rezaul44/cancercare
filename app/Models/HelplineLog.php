<?php

namespace App\Models;

use App\Enums\HelplineChannel;
use App\Enums\HelplineOutcome;
use App\Enums\HelplineTopic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HelplineLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'channel',
        'caller_name',
        'caller_phone',
        'district_id',
        'cancer_type_id',
        'topic',
        'summary_bn',
        'outcome',
        'linked_case_id',
        'handled_by',
        'follow_up_at',
        'call_duration_minutes',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'channel' => HelplineChannel::class,
            'topic' => HelplineTopic::class,
            'outcome' => HelplineOutcome::class,
            'follow_up_at' => 'date',
            'call_duration_minutes' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function cancerType(): BelongsTo
    {
        return $this->belongsTo(CancerType::class);
    }

    public function linkedCase(): BelongsTo
    {
        return $this->belongsTo(PatientCase::class, 'linked_case_id');
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
