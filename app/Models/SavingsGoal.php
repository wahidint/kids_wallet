<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingsGoal extends Model
{
    /** Icons a child can pick for a goal (Bootstrap Icons class => label). */
    public const ICONS = [
        'bi-bicycle' => 'Bicycle',
        'bi-controller' => 'Game',
        'bi-book' => 'Books',
        'bi-laptop' => 'Laptop',
        'bi-phone' => 'Phone',
        'bi-trophy' => 'Sports',
        'bi-palette' => 'Art',
        'bi-music-note-beamed' => 'Music',
        'bi-airplane' => 'Trip',
        'bi-gift' => 'Gift',
        'bi-piggy-bank' => 'Just saving',
    ];

    /** current_amount and achieved_at are changed only by SavingsService */
    protected $fillable = [
        'title',
        'icon',
        'target_amount',
        'deadline',
    ];

    protected $attributes = [
        'current_amount' => 0,
    ];

    protected function casts(): array
    {
        return [
            'target_amount' => 'decimal:2',
            'current_amount' => 'decimal:2',
            'deadline' => 'date',
            'achieved_at' => 'datetime',
        ];
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isAchieved(): bool
    {
        return $this->achieved_at !== null;
    }

    /** Money still needed, e.g. "450.00" (never negative). */
    public function remaining(): string
    {
        $left = bcsub($this->target_amount, $this->current_amount, 2);

        return bccomp($left, '0', 2) > 0 ? $left : '0.00';
    }

    /** 0–100 */
    public function percent(): int
    {
        return (int) min(100, floor(((float) $this->current_amount / (float) $this->target_amount) * 100));
    }

    /** Days until the deadline: negative = passed, 0 = today. */
    public function daysLeft(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->deadline, false);
    }

    /** Whole taka per week needed to finish on time, or null if not relevant. */
    public function weeklySuggestion(): ?int
    {
        if ($this->isAchieved() || $this->daysLeft() <= 0) {
            return null;
        }

        $weeks = max(1, (int) ceil($this->daysLeft() / 7));

        return (int) ceil((float) $this->remaining() / $weeks);
    }
}