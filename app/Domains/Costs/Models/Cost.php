<?php

namespace App\Domains\Costs\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cost extends Model
{
    use HasFactory;

    protected $fillable = [
        'cost_type',
        'amount',
        'cost_date',
    ];
}
