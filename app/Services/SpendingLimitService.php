<?php

namespace App\Services;

use App\Enums\TransactionSource;
use App\Exceptions\WalletException;
use App\Models\Wallet;
use Illuminate\Support\Carbon;

/**
 * Feature 2: Smart Spending Limits.
 * Works out how much a child has spent in each period and
 * stops any spend that would go over a limit.
 */
class SpendingLimitService
{
    /** Show a warning from this percentage of a limit. */
    public const WARNING_PERCENT = 80;

    public const PERIODS = [
        'daily' => 'Today',
        'weekly' => 'This week',
        'monthly' => 'This month',
    ];

    /** When the current period started. Weeks start on Saturday in Bangladesh. */
    public function periodStart(string $period): Carbon
    {
        return match ($period) {
            'daily' => now()->startOfDay(),
            'weekly' => now()->startOfWeek(Carbon::SATURDAY),
            'monthly' => now()->startOfMonth(),
        };
    }

    /** Total spending (not savings moves) since $start, e.g. "250.00". */
    public function spentSince(Wallet $wallet, Carbon $start): string
    {
        $sum = $wallet->transactions()
            ->debits()
            ->whereIn('source', TransactionSource::spendingValues())
            ->where('created_at', '>=', $start)
            ->sum('amount');

        return bcadd((string) $sum, '0', 2);
    }

    /**
     * One row per period that has a limit:
     * label, limit, spent, remaining, percent used and status (ok / warning / reached).
     */
    public function usage(Wallet $wallet): array
    {
        $limits = $wallet->spendingLimit;

        if (! $limits) {
            return [];
        }

        $rows = [];

        foreach (self::PERIODS as $period => $label) {
            $limit = $limits->{$period.'_limit'};

            if ($limit === null) {
                continue; // no limit for this period
            }

            $spent = $this->spentSince($wallet, $this->periodStart($period));
            $remaining = bccomp($spent, $limit, 2) >= 0 ? '0.00' : bcsub($limit, $spent, 2);
            $percent = (int) min(100, floor(((float) $spent / (float) $limit) * 100));

            $rows[] = [
                'period' => $period,
                'label' => $label,
                'limit' => $limit,
                'spent' => $spent,
                'remaining' => $remaining,
                'percent' => $percent,
                'status' => match (true) {
                    $percent >= 100 => 'reached',
                    $percent >= self::WARNING_PERCENT => 'warning',
                    default => 'ok',
                },
            ];
        }

        return $rows;
    }

    /**
     * Throws if spending $amount now would go over ANY limit.
     * Called by WalletService inside its database transaction.
     */
    public function assertWithinLimits(Wallet $wallet, string $amount): void
    {
        foreach ($this->usage($wallet) as $row) {
            if (bccomp(bcadd($row['spent'], $amount, 2), $row['limit'], 2) > 0) {
                throw WalletException::limitExceeded($row['period'], $row['remaining']);
            }
        }
    }
}