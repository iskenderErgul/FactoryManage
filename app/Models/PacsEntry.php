<?php

namespace App\Models;

use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PacsEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'entry_type',
    ];

    protected $casts = [
        'entry_type' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function logs()
    {
        return $this->hasMany(PacsEntriesLog::class);
    }
}
