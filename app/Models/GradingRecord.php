<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradingRecord extends Model
{
    use HasUuids;

    protected $fillable = [
        'batch_id',
        'grader_id',
        'moisture_pct',
        'foreign_matter_pct',
        'broken_kernels_pct',
        'grade_assigned',
        'fee_amount',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'grader_id');
    }
}
