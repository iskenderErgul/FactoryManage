<?php

namespace App\Domains\Customer\Models;

use App\Domains\Sales\Models\Sales;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'debt',
        'address',
    ];

    public function sales(): HasMany
    {
        return $this->hasMany(Sales::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(CustomersLog::class);
    }
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

}
