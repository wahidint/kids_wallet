<?php

namespace App\Exceptions;

use App\Enums\PurchaseRequestStatus;

/**
 * Friendly errors for purchase requests.
 * Extends WalletException, so it is shown as a red message automatically.
 */
class PurchaseRequestException extends WalletException
{
    public static function alreadyDecided(PurchaseRequestStatus $status): self
    {
        return new self('This request was already handled (status: '.$status->label().').');
    }

    public static function notApproved(): self
    {
        return new self('Only approved requests that haven\'t been paid yet can be completed.');
    }
}