<?php

namespace App\Services;

use App\Enums\TransactionSource;
use App\Enums\TransactionType;
use App\Exceptions\WalletException;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * THE ONLY PLACE IN THE APP WHERE A WALLET BALANCE CHANGES.
 *
 * Team rule: never write $wallet->balance = ... anywhere else.
 * Always call credit() or debit() from this service.
 */
class WalletService
{
    /**
     * Add money to a wallet (pocket money, allowance, savings withdrawal).
     */
    public function credit(
        Wallet $wallet,
        string|int|float $amount,
        TransactionSource $source,
        ?string $description = null,
        ?User $performedBy = null,
        ?Model $reference = null,
    ): Transaction {
        $this->assertDirection($source, TransactionType::Credit);

        return $this->record($wallet, $amount, $source, $description, $performedBy, $reference, null);
    }

    /**
     * Take money out of a wallet (spending, purchase, savings deposit).
     * Blocked if the wallet is locked or the balance is too low.
     */
    public function debit(
        Wallet $wallet,
        string|int|float $amount,
        TransactionSource $source,
        ?string $description = null,
        ?User $performedBy = null,
        ?Model $reference = null,
        ?int $categoryId = null,
    ): Transaction {
        $this->assertDirection($source, TransactionType::Debit);

        return $this->record($wallet, $amount, $source, $description, $performedBy, $reference, $categoryId);
    }

    /**
     * Feature 9: freeze all spending from this wallet.
     */
    public function lock(Wallet $wallet, User $parent, ?string $reason = null): void
    {
        $wallet->forceFill([
            'is_locked' => true,
            'locked_at' => now(),
            'locked_by' => $parent->getKey(),
            'lock_reason' => $reason,
        ])->save();
    }

    /**
     * Feature 9: allow spending again.
     */
    public function unlock(Wallet $wallet): void
    {
        $wallet->forceFill([
            'is_locked' => false,
            'locked_at' => null,
            'locked_by' => null,
            'lock_reason' => null,
        ])->save();
    }

    /**
     * Has a transaction already been recorded for this record?
     * Feature 4 uses this to make sure an approved purchase
     * is never deducted twice.
     */
    public function hasTransactionFor(Model $reference): bool
    {
        return Transaction::where('reference_type', $reference->getMorphClass())
            ->where('reference_id', $reference->getKey())
            ->exists();
    }

    /* ------------------------------------------------------------------ */

    /**
     * Does the actual work for credit() and debit().
     */
    private function record(
        Wallet $wallet,
        string|int|float $amount,
        TransactionSource $source,
        ?string $description,
        ?User $performedBy,
        ?Model $reference,
        ?int $categoryId,
    ): Transaction {
        $amount = $this->normalizeAmount($amount);

        // Everything inside runs as ONE database transaction:
        // if anything fails, MySQL undoes all of it.
        $transaction = DB::transaction(function () use (
            $wallet, $amount, $source, $description, $performedBy, $reference, $categoryId
        ) {
            // Re-read the wallet AND lock its row until we finish,
            // so two requests at the same moment can't both spend the same money.
            $fresh = Wallet::whereKey($wallet->getKey())->lockForUpdate()->firstOrFail();

            $type = $source->type();

            if ($type === TransactionType::Debit) {
                if ($fresh->is_locked) {
                    throw WalletException::locked();
                }

                // Feature 2 (spending limits) will add its check here.

                if (bccomp($fresh->balance, $amount, 2) < 0) {
                    throw WalletException::insufficientFunds($fresh->balance, $amount);
                }
            }

            $newBalance = $type === TransactionType::Credit
                ? bcadd($fresh->balance, $amount, 2)
                : bcsub($fresh->balance, $amount, 2);

            $fresh->forceFill(['balance' => $newBalance])->save();

            return $fresh->transactions()->create([
                'type' => $type,
                'source' => $source,
                'amount' => $amount,
                'balance_after' => $newBalance,
                'category_id' => $categoryId,
                'description' => $description,
                'performed_by' => $performedBy?->getKey(),
                'reference_type' => $reference?->getMorphClass(),
                'reference_id' => $reference?->getKey(),
            ]);
        });

        // Update the caller's $wallet object so it shows the new balance
        $wallet->refresh();

        return $transaction;
    }

    /**
     * Turn 150, 150.5 or "150.50" into the exact string "150.50".
     * Rejects zero, negatives, text, and more than 2 decimal places.
     */
    private function normalizeAmount(string|int|float $amount): string
    {
        $value = is_float($amount)
            ? number_format($amount, 2, '.', '')
            : trim((string) $amount);

        if (! preg_match('/^\d+(\.\d{1,2})?$/', $value) || bccomp($value, '0', 2) <= 0) {
            throw WalletException::invalidAmount();
        }

        return bcadd($value, '0', 2);
    }

    /**
     * Stops programming mistakes such as calling credit() with a "purchase" source.
     */
    private function assertDirection(TransactionSource $source, TransactionType $expected): void
    {
        if ($source->type() !== $expected) {
            throw new InvalidArgumentException(
                "{$source->label()} cannot be used for a {$expected->value} transaction."
            );
        }
    }
}