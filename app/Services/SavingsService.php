<?php

namespace App\Services;

use App\Enums\TransactionSource;
use App\Exceptions\SavingsException;
use App\Models\SavingsGoal;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Feature 3: Savings Goals.
 * Money moves between the wallet and a goal; both change in ONE database transaction.
 */
class SavingsService
{
    public function __construct(
        private WalletService $wallets,
    ) {
    }

    public function create(User $child, array $data): SavingsGoal
    {
        $goal = new SavingsGoal($data);
        $goal->child()->associate($child);
        $goal->save();

        return $goal;
    }

    /**
     * Wallet -> goal. Returns true if this contribution just completed the goal.
     */
    public function contribute(SavingsGoal $goal, User $child, string|int|float $amount): bool
    {
        $amount = bcadd((string) $amount, '0', 2);

        $justAchieved = DB::transaction(function () use ($goal, $child, $amount) {
            $fresh = SavingsGoal::whereKey($goal->id)->lockForUpdate()->firstOrFail();
            $remaining = $fresh->remaining();

            if (bccomp($remaining, '0', 2) <= 0) {
                throw SavingsException::alreadyReached();
            }

            if (bccomp($amount, $remaining, 2) > 0) {
                throw SavingsException::overTarget($remaining);
            }

            // Lock and balance checks happen here; any failure undoes everything
            $this->wallets->debit(
                $child->wallet, $amount, TransactionSource::SavingsDeposit,
                'Saved for: '.$fresh->title, $child, $fresh,
            );

            $newAmount = bcadd($fresh->current_amount, $amount, 2);
            $reached = bccomp($newAmount, $fresh->target_amount, 2) >= 0;

            $fresh->forceFill([
                'current_amount' => $newAmount,
                'achieved_at' => $reached ? now() : null,
            ])->save();

            return $reached;
        });

        $goal->refresh();

        return $justAchieved;
    }

    /**
     * Goal -> wallet (plans changed, or time to spend the savings).
     */
    public function withdraw(SavingsGoal $goal, User $child, string|int|float $amount): void
    {
        $amount = bcadd((string) $amount, '0', 2);

        DB::transaction(function () use ($goal, $child, $amount) {
            $fresh = SavingsGoal::whereKey($goal->id)->lockForUpdate()->firstOrFail();

            if (bccomp($amount, $fresh->current_amount, 2) > 0) {
                throw SavingsException::notEnoughSaved($fresh->current_amount);
            }

            $this->wallets->credit(
                $child->wallet, $amount, TransactionSource::SavingsWithdrawal,
                'Taken from: '.$fresh->title, $child, $fresh,
            );

            $fresh->forceFill([
                'current_amount' => bcsub($fresh->current_amount, $amount, 2),
            ])->save();
        });

        $goal->refresh();
    }

    public function delete(SavingsGoal $goal): void
    {
        if (bccomp($goal->current_amount, '0', 2) > 0) {
            throw SavingsException::notEmpty();
        }

        $goal->delete();
    }
}