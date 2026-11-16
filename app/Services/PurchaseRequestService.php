<?php

namespace App\Services;

use App\Enums\PurchaseRequestStatus;
use App\Enums\TransactionSource;
use App\Exceptions\PurchaseRequestException;
use App\Models\PurchaseRequest;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Feature 4: Purchase Approval.
 * Child requests -> parent approves/rejects -> child completes (money moves ONCE).
 */
class PurchaseRequestService
{
    public function __construct(
        private WalletService $wallets,
    ) {
    }

    /** Child asks for something. No money moves. */
    public function submit(User $child, array $data): PurchaseRequest
    {
        $purchase = new PurchaseRequest($data + ['requested_by' => $child->id]);
        $purchase->wallet()->associate($child->wallet);
        $purchase->save();

        return $purchase;
    }

    public function approve(PurchaseRequest $purchase, User $parent, ?string $note = null): void
    {
        $this->decide($purchase, $parent, PurchaseRequestStatus::Approved, $note);
    }

    public function reject(PurchaseRequest $purchase, User $parent, ?string $note = null): void
    {
        $this->decide($purchase, $parent, PurchaseRequestStatus::Rejected, $note);
    }

    /** Child withdraws a request that nobody has decided yet. */
    public function cancel(PurchaseRequest $purchase): void
    {
        DB::transaction(function () use ($purchase) {
            $fresh = PurchaseRequest::whereKey($purchase->id)->lockForUpdate()->firstOrFail();

            if ($fresh->status !== PurchaseRequestStatus::Pending) {
                throw PurchaseRequestException::alreadyDecided($fresh->status);
            }

            $fresh->forceFill(['status' => PurchaseRequestStatus::Cancelled])->save();
        });

        $purchase->refresh();
    }

    /**
     * Child bought the approved item: the money is deducted NOW, exactly once.
     */
    public function complete(PurchaseRequest $purchase, User $child): Transaction
    {
        $transaction = DB::transaction(function () use ($purchase, $child) {
            // Guard 1: lock the request row, so a double-click waits its turn
            $fresh = PurchaseRequest::whereKey($purchase->id)->lockForUpdate()->firstOrFail();

            // Guard 2: must be approved and not paid yet
            if ($fresh->status !== PurchaseRequestStatus::Approved
                || $fresh->transaction_id !== null
                || $this->wallets->hasTransactionFor($fresh)) {
                throw PurchaseRequestException::notApproved();
            }

            // Lock, balance and amount checks happen inside WalletService
            $transaction = $this->wallets->debit(
                $fresh->wallet,
                $fresh->amount,
                TransactionSource::Purchase,
                $fresh->item_name,
                $child,
                $fresh,              // reference: links the transaction to this request
                $fresh->category_id,
            );

            // Guard 3: transaction_id is UNIQUE in the database
            $fresh->forceFill([
                'status' => PurchaseRequestStatus::Completed,
                'completed_at' => now(),
                'transaction_id' => $transaction->id,
            ])->save();

            return $transaction;
        });

        $purchase->refresh();

        return $transaction;
    }

    private function decide(PurchaseRequest $purchase, User $parent, PurchaseRequestStatus $status, ?string $note): void
    {
        DB::transaction(function () use ($purchase, $parent, $status, $note) {
            $fresh = PurchaseRequest::whereKey($purchase->id)->lockForUpdate()->firstOrFail();

            // Two parents clicking at the same time: only the first decision counts
            if ($fresh->status !== PurchaseRequestStatus::Pending) {
                throw PurchaseRequestException::alreadyDecided($fresh->status);
            }

            $fresh->forceFill([
                'status' => $status,
                'parent_note' => $note,
                'decided_by' => $parent->id,
                'decided_at' => now(),
            ])->save();
        });

        $purchase->refresh();
    }
}