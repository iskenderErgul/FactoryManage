<?php

namespace App\Domains\Customer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomersLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'action',
        'changes',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
