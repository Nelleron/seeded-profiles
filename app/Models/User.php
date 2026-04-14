<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RegistrationStep;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'email',
        'password',
        'registration_step',
        'is_seeded',
        'banned_at',
        'last_active_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'is_seeded' => 'boolean',
        'registration_step' => RegistrationStep::class,
        'banned_at' => 'datetime',
        'last_active_at' => 'datetime',
    ];

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(UserPhoto::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }
}
