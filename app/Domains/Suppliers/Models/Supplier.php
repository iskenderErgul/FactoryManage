<?php

namespace App\Domains\Suppliers\Models;

use App\Domains\Customer\Models\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $guarded = [ ];

    public function customer()
    {
        return $this->belongsTo(Customer::class); // Her tedarikçi bir müşteriyle ilişkilidir
    }
}
