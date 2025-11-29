<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'type',
        'product',
        'quantity',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

