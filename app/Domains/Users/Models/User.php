<?php

namespace App\Domains\Users\Models;

use App\Domains\PacsEntry\Models\PacsEntry;
use App\Domains\Production\Models\Production;
use App\Domains\Shift\Models\Shift;
use App\Domains\Shift\Models\ShiftAssignment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'photo',
        'role',
    ];

    protected $casts = [
        'role' => 'string',
    ];

    public function pacsEntries(): HasMany
    {
        return $this->hasMany(PacsEntry::class);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    public function productions(): HasMany
    {
        return $this->hasMany(Production::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(UsersLog::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ShiftAssignment::class);
    }



}
