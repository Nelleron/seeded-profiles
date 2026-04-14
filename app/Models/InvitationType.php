<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvitationType extends Model
{
    protected $fillable = ['slug', 'name', 'emoji', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class, 'type_id');
    }
}
