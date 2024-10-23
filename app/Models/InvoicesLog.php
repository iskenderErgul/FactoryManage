<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoicesLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'action',
        'changes',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
