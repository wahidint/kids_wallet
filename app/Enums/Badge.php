<?php

namespace App\Enums;

/**
 * Feature 10: every badge a child can earn.
 * Add a new case here to create a new badge.
 */
enum Badge: string
{
    case FirstSavings = 'first_savings';
    case ConsistentSaver = 'consistent_saver';
    case GoalAchiever = 'goal_achiever';
    case SuperSaver = 'super_saver';
    case SmartSpender = 'smart_spender';

    public function label(): string
    {
        return match ($this) {
            self::FirstSavings => 'First Savings',
            self::ConsistentSaver => 'Consistent Saver',
            self::GoalAchiever => 'Goal Achiever',
            self::SuperSaver => 'Super Saver',
            self::SmartSpender => 'Smart Spender',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::FirstSavings => 'Saved money into a goal for the first time.',
            self::ConsistentSaver => 'Saved money in 3 different weeks.',
            self::GoalAchiever => 'Reached a savings goal.',
            self::SuperSaver => 'Saved ৳1,000 in total.',
            self::SmartSpender => 'Spent less than half of the money received in a month.',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::FirstSavings => 'bi-piggy-bank-fill',
            self::ConsistentSaver => 'bi-calendar-check-fill',
            self::GoalAchiever => 'bi-trophy-fill',
            self::SuperSaver => 'bi-gem',
            self::SmartSpender => 'bi-lightbulb-fill',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::FirstSavings => '#E2136E',
            self::ConsistentSaver => '#7C3AED',
            self::GoalAchiever => '#F59E0B',
            self::SuperSaver => '#0EA5E9',
            self::SmartSpender => '#16A34A',
        };
    }

    /** Which number from RewardService::metrics() this badge is measured by. */
    public function metric(): string
    {
        return match ($this) {
            self::FirstSavings => 'deposits',
            self::ConsistentSaver => 'deposit_weeks',
            self::GoalAchiever => 'goals_achieved',
            self::SuperSaver => 'deposit_total',
            self::SmartSpender => 'smart_month',
        };
    }

    /** The number needed to earn it. */
    public function target(): int
    {
        return match ($this) {
            self::ConsistentSaver => 3,
            self::SuperSaver => 1000,
            default => 1,
        };
    }

    /** e.g. "2 of 3 weeks" or "৳450 of ৳1,000" */
    public function progressLabel(float $current): string
    {
        return match ($this) {
            self::FirstSavings => 'Save into any goal',
            self::ConsistentSaver => (int) $current.' of 3 weeks',
            self::GoalAchiever => 'Reach any savings goal',
            self::SuperSaver => '৳'.number_format($current).' of ৳1,000',
            self::SmartSpender => 'Spend less than half of last month\'s money',
        };
    }
}