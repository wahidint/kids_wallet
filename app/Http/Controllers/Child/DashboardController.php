<?php

namespace App\Http\Controllers\Child;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $child = $request->user();
        $wallet = $child->wallet->load('lockedBy');
        $monthStart = now()->startOfMonth();

        $monthIncome = $wallet->transactions()->credits()
            ->where('created_at', '>=', $monthStart)->sum('amount');

        $monthSpending = $wallet->transactions()->debits()
            ->where('created_at', '>=', $monthStart)->sum('amount');

        $recentTransactions = $wallet->transactions()
            ->with('category')
            ->latest()
            ->latest('id')
            ->take(8)
            ->get();

        $parents = $child->parents()->orderBy('name')->get();

        return view('child.dashboard', compact(
            'child', 'wallet', 'monthIncome', 'monthSpending', 'recentTransactions', 'parents'
        ));
    }
}