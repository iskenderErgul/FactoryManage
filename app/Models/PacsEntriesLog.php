<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PacsEntriesLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'pacs_entry_id',
        'action',
        'changes',
    ];

    public function pacsEntry()
    {
        return $this->belongsTo(PacsEntry::class);
    }
}
