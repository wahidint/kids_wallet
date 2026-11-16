<?php

namespace App\Models;

use App\Services\SpendingLimitService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    /** Daily / weekly / monthly limits (Feature 2). */
    public function spendingLimit(): HasOne
    {
        return $this->hasOne(SpendingLimit::class);
    }

    /**
     * Spending vs. limits for each period that has a limit.
     * Used by the parent wallet page, child dashboard and spend page.
     */
    public function limitUsage(): array
    {
        return app(SpendingLimitService::class)->usage($this);
    }
}