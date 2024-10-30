<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory;

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

    public function pacsEntries()
    {
        return $this->hasMany(PacsEntry::class);
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function productions()
    {
        return $this->hasMany(Production::class);
    }

    public function logs()
    {
        return $this->hasMany(UsersLog::class);
    }



}
