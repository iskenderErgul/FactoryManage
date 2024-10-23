<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'action',
        'changes',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
