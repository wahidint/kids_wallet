<?php

namespace App\Exceptions;

/**
 * Friendly savings errors. Extends WalletException, so they show as red messages.
 */
class SavingsException extends WalletException
{
    public static function overTarget(string $remaining): self
    {
        return new self("That's more than you need! Only ৳{$remaining} left to reach this goal.");
    }

    public static function alreadyReached(): self
    {
        return new self('You already reached this goal. Well done!');
    }

    public static function notEnoughSaved(string $saved): self
    {
        return new self("You only have ৳{$saved} saved in this goal.");
    }

    public static function notEmpty(): self
    {
        return new self('Take the saved money out first, then you can delete this goal.');
    }
}