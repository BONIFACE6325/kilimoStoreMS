<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtherIncome extends Model
{
    protected $fillable = [
        'tenant_id',
        'source_name',
        'amount',
        'date_received',
        'description',
        'recorded_by'
    ];
}
