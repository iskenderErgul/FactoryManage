<?php

namespace App\Domains\Customer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;


    protected  $guarded = [''];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

}
