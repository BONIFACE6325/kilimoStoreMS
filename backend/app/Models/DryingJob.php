<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DryingJob extends Model
{
    use HasUuids;

    protected $fillable = [
        'batch_id',
        'machine_id',
        'start_time',
        'end_time',
        'initial_moisture',
        'final_moisture',
        'weight_before_mt',
        'weight_after_mt',
        'fee_amount',
        'status',
        'service_id',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
