<?php

namespace App\Enums;

/**
 * Why a parent is adding money. Shown as choices on the "Add money" page
 * and saved as the transaction's description.
 */
enum PocketMoneyOccasion: string
{
    case PocketMoney = 'pocket_money';
    case EidSalami = 'eid_salami';
    case Birthday = 'birthday';
    case GoodResults = 'good_results';
    case JustBecause = 'just_because';

    public function label(): string
    {
        return match ($this) {
            self::PocketMoney => 'Pocket money',
            self::EidSalami => 'Eid salami',
            self::Birthday => 'Birthday gift',
            self::GoodResults => 'Reward for good results',
            self::JustBecause => 'Just because',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PocketMoney => 'bi-cash-coin',
            self::EidSalami => 'bi-moon-stars',
            self::Birthday => 'bi-cake2',
            self::GoodResults => 'bi-trophy',
            self::JustBecause => 'bi-gift',
        };
    }
}