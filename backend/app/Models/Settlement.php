<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Settlement extends Model
{
    use HasUuids;

    protected $fillable = [
        'tenant_id',
        'farmer_id',
        'invoice_id',
        'gross_amount',
        'total_deductions',
        'net_payout',
        'payment_method',
        'payment_status',
        'payment_reference',
        'settled_at',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function deductions(): HasMany
    {
        return $this->hasMany(SettlementDeduction::class);
    }
}
