<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchMovement extends Model
{
    use HasUuids;

    protected $fillable = [
        'batch_id',
        'source_bin_id',
        'destination_bin_id',
        'moved_by',
        'quantity_mt',
        'reason',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function sourceBin(): BelongsTo
    {
        return $this->belongsTo(Bin::class, 'source_bin_id');
    }

    public function destinationBin(): BelongsTo
    {
        return $this->belongsTo(Bin::class, 'destination_bin_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moved_by');
    }
}
