<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'related_id',
        'related_process',
        'amount',
        'invoice_date',
    ];

    protected $casts = [
        'type' => 'string',
    ];

    public function logs()
    {
        return $this->hasMany(InvoicesLog::class);
    }
}
