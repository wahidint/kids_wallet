<?php

namespace App\Http\Controllers\Child;

use App\Enums\TransactionSource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Child\SpendRequest;
use App\Models\SpendingCategory;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SpendController extends Controller
{
    public function create(Request $request): View
    {
        return view('child.spend.create', [
            'wallet' => $request->user()->wallet->load('lockedBy'),
            'categories' => SpendingCategory::orderBy('id')->get(),
        ]);
    }

    public function store(SpendRequest $request, WalletService $wallets): RedirectResponse
    {
        $child = $request->user();
        $data = $request->validated();
        $category = SpendingCategory::findOrFail($data['category_id']);

        // Throws a WalletException (shown as a red message) if the wallet is locked
        // or the balance is too low. Nothing is saved in that case.
        $transaction = $wallets->debit(
            $child->wallet,
            $data['amount'],
            TransactionSource::Spend,
            $data['description'],
            $child,
            null,
            $category->id,
        );

        return redirect()->route('child.dashboard')->with('success',
            'You spent ৳'.number_format($transaction->amount, 2).' on '.$category->name
            .'. Balance left: ৳'.number_format($child->wallet->balance, 2).'.'
        );
    }
}