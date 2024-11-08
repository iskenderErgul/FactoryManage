<?php

namespace App\Domains\Costs\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cost extends Model
{
    protected $table = 'costs';
    use HasFactory;

    protected $fillable = [
        'cost_type',
        'amount',
        'cost_date',
        'id',
        'created_at',
        'updated_at',
    ];
}
