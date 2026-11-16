<?php

namespace App\Models;

use App\Enums\PurchaseRequestStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRequest extends Model
{
    /** status, decision and transaction fields are changed only by PurchaseRequestService */
    protected $fillable = [
        'requested_by',
        'category_id',
        'item_name',
        'amount',
        'description',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => PurchaseRequestStatus::class,
            'decided_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /* ---------------- Relationships ---------------- */

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    /** The child who asked. */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(SpendingCategory::class, 'category_id');
    }

    /** The parent who approved or rejected it. */
    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /* ---------------- Query scopes ---------------- */

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', PurchaseRequestStatus::Pending->value);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', PurchaseRequestStatus::Approved->value);
    }

    /** Requests from any child linked to this parent. */
    public function scopeForParent(Builder $query, User $parent): Builder
    {
        $walletIds = Wallet::whereIn('user_id', $parent->children()->pluck('users.id'))->pluck('id');

        return $query->whereIn('wallet_id', $walletIds);
    }

    /* ---------------- Helpers for views ---------------- */

    public function isPending(): bool
    {
        return $this->status === PurchaseRequestStatus::Pending;
    }

    public function isApproved(): bool
    {
        return $this->status === PurchaseRequestStatus::Approved;
    }

    public function isCompleted(): bool
    {
        return $this->status === PurchaseRequestStatus::Completed;
    }
}