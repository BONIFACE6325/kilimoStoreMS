<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Batch extends Model
{
    use HasUuids;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'farmer_id',
        'batch_code',
        'crop_type',
        'variety',
        'intake_quantity',
        'intake_unit',
        'initial_moisture',
        'current_moisture',
        'initial_weight_mt',
        'current_weight_mt',
        'current_bin_id',
        'status',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }

    public function bin(): BelongsTo
    {
        return $this->belongsTo(Bin::class, 'current_bin_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(BatchMovement::class);
    }

    public function dryingJobs(): HasMany
    {
        return $this->hasMany(DryingJob::class);
    }

    public function millingJobs(): HasMany
    {
        return $this->hasMany(MillingJob::class);
    }

    public function gradingRecord(): HasOne
    {
        return $this->hasOne(GradingRecord::class);
    }
}
