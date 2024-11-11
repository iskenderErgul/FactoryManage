<?php

namespace App\Domains\Production\Models;

use App\Domains\Machines\Models\Machine;
use App\Domains\Product\Models\Product;
use App\Domains\Shift\Models\Shift;
use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shift(): BelongsTo
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
