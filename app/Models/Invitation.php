<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invitation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'type_id', 'description', 'location',
        'distance', 'city_id', 'approved_by',
    ];

    protected $casts = [
        'distance' => 'decimal:1',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(InvitationType::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
