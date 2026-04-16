<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    protected $fillable = ['name', 'country'];

    public function profiles(): HasMany
    {
        return $this->hasMany(UserProfile::class);
    }
}
