<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Errors a user can cause and should be told about,
 * e.g. "not enough balance" or "wallet is locked".
 */
class WalletException extends Exception
{
    public static function locked(): self
    {
        return new self('This wallet is locked by a parent. Spending is paused until it is unlocked.');
    }

    public static function insufficientFunds(string $balance, string $amount): self
    {
        return new self("Not enough balance. Available: ৳{$balance}, needed: ৳{$amount}.");
    }

    public static function invalidAmount(): self
    {
        return new self('Amount must be greater than zero, with at most 2 decimal places.');
    }

    /**
     * If a WalletException is not caught in a controller, Laravel calls this:
     * the user is sent back to the form with the error message shown,
     * and what they typed is kept.
     */
    public function render(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $this->getMessage()], 422);
        }

        return back()->withInput()->with('error', $this->getMessage());
    }
}