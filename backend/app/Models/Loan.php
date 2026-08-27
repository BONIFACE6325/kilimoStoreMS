<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    use HasUuids;

    protected $fillable = [
        'tenant_id',
        'farmer_id',
        'collateral_batch_id',
        'loan_code',
        'principal_amount',
        'interest_rate_annual',
        'current_balance',
        'accrued_interest',
        'disbursed_at',
        'due_date',
        'status',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }

    public function collateralBatch(): BelongsTo
    {
        return $this->belongsTo(Batch::class, 'collateral_batch_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(LoanTransaction::class);
    }
}
