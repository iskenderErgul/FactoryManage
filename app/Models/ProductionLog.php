<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_id',
        'action',
        'changes',
        'user_id'
    ];

    public function production()
    {
        return $this->belongsTo(Production::class);
    }

    public  function user(){
        return $this->belongsTo(User::class);
    }
}
