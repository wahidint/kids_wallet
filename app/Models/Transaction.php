<?php

namespace App\Models;

use App\Enums\TransactionSource;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LogicException;

class Transaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'type',
        'source',
        'amount',
        'balance_after',
        'category_id',
        'description',
        'performed_by',
        'reference_type',
        'reference_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'source' => TransactionSource::class,
            'amount' => 'decimal:2',
            'balance_after' => 'decimal:2',
        ];
    }

    /**
     * Transactions are permanent history, like a bank statement.
     * Any attempt to edit one is stopped immediately.
     */
    protected static function booted(): void
    {
        static::updating(function () {
            throw new LogicException('Transactions are permanent records and cannot be edited.');
        });
    }

    /* ---------------- Relationships ---------------- */

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(SpendingCategory::class, 'category_id');
    }

    /** The user who caused it, e.g. the parent who added money. */
    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /** The record that caused it: a PurchaseRequest, SavingsGoal, etc. */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /* ---------------- Query scopes ---------------- */

    /** Usage: $wallet->transactions()->credits()->get() */
    public function scopeCredits(Builder $query): Builder
    {
        return $query->where('type', TransactionType::Credit);
    }

    /** Usage: $wallet->transactions()->debits()->get() */
    public function scopeDebits(Builder $query): Builder
    {
        return $query->where('type', TransactionType::Debit);
    }
}