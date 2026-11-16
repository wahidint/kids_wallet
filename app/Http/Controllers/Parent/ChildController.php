<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class ChildController extends Controller
{
    /**
     * One child's wallet, as seen by their parent.
     * The route's "can:manage-child" check runs before this.
     */
    public function show(User $child): View
    {
        $wallet = $child->wallet->load('lockedBy');
        $monthStart = now()->startOfMonth();

        $monthIncome = $wallet->transactions()->credits()
            ->where('created_at', '>=', $monthStart)->sum('amount');

        $monthSpending = $wallet->transactions()->debits()
            ->where('created_at', '>=', $monthStart)->sum('amount');

        $transactions = $wallet->transactions()
            ->with('category')
            ->latest()
            ->latest('id')
            ->paginate(10);

        return view('parent.children.show', compact(
            'child', 'wallet', 'monthIncome', 'monthSpending', 'transactions'
        ));
    }
}