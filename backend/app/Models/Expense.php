<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'tenant_id',
        'category_name',
        'amount',
        'date_incurred',
        'description',
        'recorded_by'
    ];
}
