<?php

namespace App\Http\Controllers\Child;

use App\Http\Controllers\Controller;
use App\Models\SpendingCategory;
use App\Services\TransactionHistoryService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionController extends Controller
{
    public function index(Request $request, TransactionHistoryService $history): View
    {
        $filters = $history->filtersFrom($request);
        $query = $history->query($request->user()->wallet, $filters);

        return view('child.transactions.index', [
            'transactions' => (clone $query)->latest()->latest('id')->paginate(15)->withQueryString(),
            'summary' => $history->summary($query),
            'filters' => $filters,
            'categories' => SpendingCategory::orderBy('id')->get(),
        ]);
    }

    public function export(Request $request, TransactionHistoryService $history): StreamedResponse
    {
        $query = $history->query($request->user()->wallet, $history->filtersFrom($request));

        return $history->csv($query, 'my-transactions-'.now()->format('Y-m-d').'.csv');
    }
}