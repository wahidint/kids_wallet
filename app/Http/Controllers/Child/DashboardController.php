<?php

namespace App\Http\Controllers\Child;

use App\Http\Controllers\Controller;
use App\Models\SavingsGoal;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $child = $request->user();
        $wallet = $child->wallet->load('lockedBy');
        $monthStart = now()->startOfMonth();

        // Real income and expenses only (savings moves are neither)
        $monthIncome = $wallet->transactions()->income()
            ->where('created_at', '>=', $monthStart)->sum('amount');

        $monthSpending = $wallet->transactions()->expenses()
            ->where('created_at', '>=', $monthStart)->sum('amount');

        $recentTransactions = $wallet->transactions()
            ->with('category')
            ->latest()
            ->latest('id')
            ->take(8)
            ->get();

        $parents = $child->parents()->orderBy('name')->get();

        $goals = SavingsGoal::where('user_id', $child->id)
            ->orderByRaw('achieved_at IS NOT NULL')
            ->orderBy('deadline')
            ->get();

        $totalSaved = $goals->reduce(fn (string $sum, SavingsGoal $goal) => bcadd($sum, $goal->current_amount, 2), '0.00');

        return view('child.dashboard', compact(
            'child', 'wallet', 'monthIncome', 'monthSpending', 'recentTransactions', 'parents', 'goals', 'totalSaved'
        ));
    }
}