<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\SavingsGoal;
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

        $monthIncome = $wallet->transactions()->income()
            ->where('created_at', '>=', $monthStart)->sum('amount');

        $monthSpending = $wallet->transactions()->expenses()
            ->where('created_at', '>=', $monthStart)->sum('amount');

        $transactions = $wallet->transactions()
            ->with('category')
            ->latest()
            ->latest('id')
            ->paginate(10);

        $goals = SavingsGoal::where('user_id', $child->id)
            ->orderByRaw('achieved_at IS NOT NULL')
            ->orderBy('deadline')
            ->get();

        $totalSaved = $goals->reduce(fn (string $sum, SavingsGoal $goal) => bcadd($sum, $goal->current_amount, 2), '0.00');

        return view('parent.children.show', compact(
            'child', 'wallet', 'monthIncome', 'monthSpending', 'transactions', 'goals', 'totalSaved'
        ));
    }
}