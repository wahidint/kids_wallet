<?php

namespace App\Services;

use App\Enums\Badge;
use App\Enums\TransactionSource;
use App\Models\SavingsGoal;
use App\Models\User;
use App\Models\UserBadge;

/**
 * Feature 10: Savings Rewards.
 * Measures a child's habits and awards badges they've earned (once each).
 */
class RewardService
{
    /**
     * Award every badge the child has newly earned.
     * Returns the NEW badges (empty array if none).
     */
    public function evaluate(User $child): array
    {
        if (! $child->isChild()) {
            return [];
        }

        $alreadyEarned = UserBadge::where('user_id', $child->id)->toBase()->pluck('badge')->all();
        $metrics = null; // calculated only if a badge is still missing
        $new = [];

        foreach (Badge::cases() as $badge) {
            if (in_array($badge->value, $alreadyEarned, true)) {
                continue;
            }

            $metrics ??= $this->metrics($child);

            if ((float) $metrics[$badge->metric()] >= $badge->target()) {
                // createOrFirst + the unique index = never a duplicate, even with two tabs open
                $record = UserBadge::createOrFirst(
                    ['user_id' => $child->id, 'badge' => $badge->value],
                    ['earned_at' => now()],
                );

                if ($record->wasRecentlyCreated) {
                    $new[] = $badge;
                }
            }
        }

        return $new;
    }

    /** Every badge with its progress, for the Rewards page. */
    public function progress(User $child): array
    {
        $metrics = $this->metrics($child);
        $earned = UserBadge::where('user_id', $child->id)->get()->keyBy(fn (UserBadge $b) => $b->badge->value);

        return array_map(function (Badge $badge) use ($metrics, $earned) {
            $record = $earned->get($badge->value);
            $current = min((float) $metrics[$badge->metric()], $badge->target());

            return [
                'badge' => $badge,
                'earned' => $record,
                'current' => $current,
                'percent' => $record ? 100 : (int) floor($current / $badge->target() * 100),
            ];
        }, Badge::cases());
    }

    /** The numbers every badge rule is measured against. */
    private function metrics(User $child): array
    {
        $wallet = $child->wallet;
        $deposit = TransactionSource::SavingsDeposit->value;

        $lastMonthStart = now()->subMonthNoOverflow()->startOfMonth();
        $thisMonthStart = now()->startOfMonth();

        $lastMonthIncome = (float) $wallet->transactions()->income()
            ->where('created_at', '>=', $lastMonthStart)->where('created_at', '<', $thisMonthStart)
            ->sum('amount');

        $lastMonthExpenses = (float) $wallet->transactions()->expenses()
            ->where('created_at', '>=', $lastMonthStart)->where('created_at', '<', $thisMonthStart)
            ->sum('amount');

        return [
            'deposits' => $wallet->transactions()->where('source', $deposit)->count(),
            'deposit_weeks' => (int) $wallet->transactions()->where('source', $deposit)
                ->selectRaw('COUNT(DISTINCT YEARWEEK(created_at)) AS weeks')->value('weeks'),
            'deposit_total' => (float) $wallet->transactions()->where('source', $deposit)->sum('amount'),
            'goals_achieved' => SavingsGoal::where('user_id', $child->id)->whereNotNull('achieved_at')->count(),
            'smart_month' => ($lastMonthIncome >= 100 && $lastMonthExpenses <= $lastMonthIncome / 2) ? 1 : 0,
        ];
    }
}