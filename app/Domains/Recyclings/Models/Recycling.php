<?php

namespace App\Domains\Recyclings\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recycling extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'material_type',
        'recycling_date',
        'recycling_quantity',
    ];
}
