<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpendingLimit extends Model
{
    protected $fillable = [
        'daily_limit',
        'weekly_limit',
        'monthly_limit',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'daily_limit' => 'decimal:2',
            'weekly_limit' => 'decimal:2',
            'monthly_limit' => 'decimal:2',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}