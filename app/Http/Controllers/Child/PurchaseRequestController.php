<?php

namespace App\Http\Controllers\Child;

use App\Http\Controllers\Controller;
use App\Http\Requests\Child\StorePurchaseRequest;
use App\Models\PurchaseRequest;
use App\Models\SpendingCategory;
use App\Services\PurchaseRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseRequestController extends Controller
{
    public function index(Request $request): View
    {
        $child = $request->user();

        return view('child.purchases.index', [
            'wallet' => $child->wallet,
            'requests' => PurchaseRequest::with(['category', 'decidedBy'])
                ->where('requested_by', $child->id)
                ->latest()
                ->latest('id')
                ->get(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('child.purchases.create', [
            'wallet' => $request->user()->wallet,
            'categories' => SpendingCategory::orderBy('id')->get(),
        ]);
    }

    public function store(StorePurchaseRequest $request, PurchaseRequestService $purchases): RedirectResponse
    {
        $purchase = $purchases->submit($request->user(), $request->validated());

        return redirect()->route('child.purchases.index')
            ->with('success', "Your request for {$purchase->item_name} was sent to your parents.");
    }

    public function complete(Request $request, PurchaseRequest $purchase, PurchaseRequestService $purchases): RedirectResponse
    {
        $purchases->complete($purchase, $request->user());

        return redirect()->route('child.purchases.index')
            ->with('success', "Enjoy your {$purchase->item_name}! ৳".number_format($purchase->amount, 2).' was paid from your wallet.');
    }

    public function cancel(PurchaseRequest $purchase, PurchaseRequestService $purchases): RedirectResponse
    {
        $purchases->cancel($purchase);

        return redirect()->route('child.purchases.index')
            ->with('success', "Request for {$purchase->item_name} cancelled.");
    }
}