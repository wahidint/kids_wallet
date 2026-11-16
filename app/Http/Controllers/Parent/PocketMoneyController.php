<?php

namespace App\Http\Controllers\Parent;

use App\Enums\PocketMoneyOccasion;
use App\Enums\TransactionSource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Parent\AddMoneyRequest;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PocketMoneyController extends Controller
{
    public function create(User $child): View
    {
        return view('parent.pocket-money.create', [
            'child' => $child,
            'wallet' => $child->wallet,
            'occasions' => PocketMoneyOccasion::cases(),
        ]);
    }

    public function store(AddMoneyRequest $request, User $child, WalletService $wallets): RedirectResponse
    {
        $data = $request->validated();
        $occasion = PocketMoneyOccasion::from($data['occasion']);
        $note = trim($data['note'] ?? '');

        // e.g. "Eid salami — Eid Mubarak!"
        $description = $note === '' ? $occasion->label() : $occasion->label().' — '.$note;

        $transaction = $wallets->credit(
            $child->wallet,
            $data['amount'],
            TransactionSource::PocketMoney,
            $description,
            $request->user(),
        );

        return redirect()
            ->route('parent.children.show', $child)
            ->with('success', '৳'.number_format($transaction->amount, 2)." added to {$child->name}'s wallet.");
    }
}