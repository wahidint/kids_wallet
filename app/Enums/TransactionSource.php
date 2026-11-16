<?php

namespace App\Enums;

/**
 * WHY money moved. Every transaction has exactly one source.
 *
 * Team rule: when a new feature needs a new kind of transaction
 * (e.g. "send money" in a later sprint), add a new case here.
 */
enum TransactionSource: string
{
    case PocketMoney = 'pocket_money';             // F1: parent adds money manually
    case Allowance = 'allowance';                  // F7: scheduled pocket money
    case Spend = 'spend';                          // F2/F5: child records a spend
    case Purchase = 'purchase';                    // F4: approved purchase request
    case SavingsDeposit = 'savings_deposit';       // F3: wallet -> savings goal
    case SavingsWithdrawal = 'savings_withdrawal'; // F3: savings goal -> wallet

    /**
     * Each source always moves money in one direction.
     * The WalletService uses this so a "purchase" can never accidentally ADD money.
     */
    public function type(): TransactionType
    {
        return match ($this) {
            self::PocketMoney,
            self::Allowance,
            self::SavingsWithdrawal => TransactionType::Credit,

            self::Spend,
            self::Purchase,
            self::SavingsDeposit => TransactionType::Debit,
        };
    }

    /**
     * Does this count toward spending limits (F2)?
     * Moving money into savings is NOT spending.
     */
    public function countsAsSpending(): bool
    {
        return in_array($this, [self::Spend, self::Purchase], true);
    }

    /** Database values of all spending sources, e.g. ['spend', 'purchase']. */
    public static function spendingValues(): array
    {
        return array_values(array_map(
            fn (self $source) => $source->value,
            array_filter(self::cases(), fn (self $source) => $source->countsAsSpending())
        ));
    }

    public function label(): string
    {
        return match ($this) {
            self::PocketMoney => 'Pocket Money',
            self::Allowance => 'Allowance',
            self::Spend => 'Spending',
            self::Purchase => 'Approved Purchase',
            self::SavingsDeposit => 'Moved to Savings',
            self::SavingsWithdrawal => 'Withdrawn from Savings',
        };
    }
}