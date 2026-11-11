<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $parent = $request->user();

        // Only THIS parent's children: never another family's
        $children = $parent->children()->with('wallet')->orderBy('name')->get();
        $walletIds = $children->pluck('wallet.id')->filter();

        $totalBalance = $children->reduce(
            fn (string $sum, $child) => bcadd($sum, $child->wallet?->balance ?? '0', 2),
            '0.00'
        );

        $monthSpending = Transaction::whereIn('wallet_id', $walletIds)
            ->debits()
            ->where('created_at', '>=', now()->startOfMonth())
            ->sum('amount');

        $recentTransactions = Transaction::with(['wallet.child', 'category'])
            ->whereIn('wallet_id', $walletIds)
            ->latest()
            ->latest('id')
            ->take(8)
            ->get();

        return view('parent.dashboard', compact(
            'parent', 'children', 'totalBalance', 'monthSpending', 'recentTransactions'
        ));
    }
}