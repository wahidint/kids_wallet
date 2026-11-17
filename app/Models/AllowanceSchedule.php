<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;

class AllowanceSchedule extends Model
{
    public const WEEKLY = 'weekly';
    public const MONTHLY = 'monthly';

    /** Carbon day numbers, listed Saturday-first (Bangladesh week). */
    public const WEEKDAYS = [6 => 'Saturday', 0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday'];

    protected $fillable = [
        'amount',
        'frequency',
        'day_of_week',
        'day_of_month',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'day_of_week' => 'integer',
            'day_of_month' => 'integer',
            'next_run_at' => 'date',
            'last_run_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(AllowancePayment::class);
    }

    public function isWeekly(): bool
    {
        return $this->frequency === self::WEEKLY;
    }

    /** "Every Friday" or "Monthly on the 1st" */
    public function scheduleLabel(): string
    {
        return $this->isWeekly()
            ? 'Every '.self::WEEKDAYS[$this->day_of_week]
            : 'Monthly on the '.Number::ordinal($this->day_of_month);
    }

    /** Shown as the transaction description. */
    public function description(): string
    {
        return $this->note ?: ($this->isWeekly() ? 'Weekly allowance' : 'Monthly allowance');
    }

    /** First due date on or after $from. */
    public function firstDateFrom(Carbon $from): Carbon
    {
        $from = $from->copy()->startOfDay();

        if ($this->isWeekly()) {
            $date = $from->copy();
            while ($date->dayOfWeek !== $this->day_of_week) {
                $date->addDay();
            }

            return $date;
        }

        $date = $from->copy()->day($this->day_of_month);

        return $date->lt($from) ? $date->addMonthNoOverflow()->day($this->day_of_month) : $date;
    }

    /** The due date after $due. */
    public function followingDate(Carbon $due): Carbon
    {
        return $this->isWeekly()
            ? $due->copy()->addWeek()
            : $due->copy()->addMonthNoOverflow()->day($this->day_of_month);
    }
}