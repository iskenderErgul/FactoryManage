<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Production extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'machine_id',
        'product_id',
        'quantity',
        'shift_id',
        'production_date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function logs()
    {
        return $this->hasMany(ProductionLog::class);
    }
    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }




}
