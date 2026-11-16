<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Http\Requests\Parent\LockWalletRequest;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;

/**
 * Feature 9: Emergency Wallet Lock.
 */
class WalletLockController extends Controller
{
    /** Lock the wallet: spending stops immediately. */
    public function store(LockWalletRequest $request, User $child, WalletService $wallets): RedirectResponse
    {
        if ($child->wallet->is_locked) {
            return $this->backToWallet($child, 'error', "{$child->name}'s wallet is already locked.");
        }

        $wallets->lock($child->wallet, $request->user(), $request->validated('reason'));

        return $this->backToWallet($child, 'success', "{$child->name}'s wallet is now locked. Spending is paused until you unlock it.");
    }

    /** Unlock the wallet: spending is allowed again. */
    public function destroy(User $child, WalletService $wallets): RedirectResponse
    {
        if (! $child->wallet->is_locked) {
            return $this->backToWallet($child, 'error', "{$child->name}'s wallet is not locked.");
        }

        $wallets->unlock($child->wallet);

        return $this->backToWallet($child, 'success', "{$child->name}'s wallet is unlocked. Spending is allowed again.");
    }

    private function backToWallet(User $child, string $type, string $message): RedirectResponse
    {
        return redirect()->route('parent.children.show', $child)->with($type, $message);
    }
}