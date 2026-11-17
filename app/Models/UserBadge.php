<?php

namespace App\Models;

use App\Enums\Badge;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBadge extends Model
{
    protected $fillable = ['user_id', 'badge', 'earned_at'];

    protected function casts(): array
    {
        return [
            'badge' => Badge::class,
            'earned_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}