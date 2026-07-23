<?php

namespace App\Models;

use App\Enum\RoleEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasUuids;

    protected $fillable = [
        'name',
        'surname',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'role' => RoleEnum::class,
    ];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'parent_id', 'id');
    }

    public function getJWTIdentifier()
    {
       return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
       return [];
    }
}
