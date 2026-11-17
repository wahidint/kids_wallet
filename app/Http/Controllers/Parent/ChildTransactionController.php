<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\SpendingCategory;
use App\Models\User;
use App\Services\TransactionHistoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * A parent's view of one child's full history.
 * The route's "can:manage-child" check runs before this.
 */
class ChildTransactionController extends Controller
{
    public function index(Request $request, User $child, TransactionHistoryService $history): View
    {
        $filters = $history->filtersFrom($request);
        $query = $history->query($child->wallet, $filters);

        return view('parent.children.transactions', [
            'child' => $child,
            'transactions' => (clone $query)->latest()->latest('id')->paginate(15)->withQueryString(),
            'summary' => $history->summary($query),
            'filters' => $filters,
            'categories' => SpendingCategory::orderBy('id')->get(),
        ]);
    }

    public function export(Request $request, User $child, TransactionHistoryService $history): StreamedResponse
    {
        $query = $history->query($child->wallet, $history->filtersFrom($request));

        return $history->csv($query, Str::slug($child->name).'-transactions-'.now()->format('Y-m-d').'.csv');
    }
}