<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    /**
     * Deliberately EMPTY: no column can be mass-assigned from a form.
     * The balance may only change inside the WalletService,
     * which also records a transaction every time.
     */
    protected $fillable = [];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'is_locked' => 'boolean',
            'locked_at' => 'datetime',
        ];
    }

    /** The child who owns this wallet. */
    public function child(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /** The parent who locked it (Feature 9). */
    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }
}