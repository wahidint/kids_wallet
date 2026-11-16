<?php

namespace App\Http\Controllers\Parent;

use App\Enums\PurchaseRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Parent\DecidePurchaseRequest;
use App\Models\PurchaseRequest;
use App\Services\PurchaseRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseApprovalController extends Controller
{
    public function index(Request $request): View
    {
        $parent = $request->user();
        $with = ['requester', 'category', 'wallet', 'decidedBy'];

        return view('parent.purchases.index', [
            'pending' => PurchaseRequest::forParent($parent)->with($with)->pending()->oldest()->get(),
            'history' => PurchaseRequest::forParent($parent)->with($with)
                ->where('status', '!=', PurchaseRequestStatus::Pending->value)
                ->latest('updated_at')
                ->take(20)
                ->get(),
        ]);
    }

    public function approve(DecidePurchaseRequest $request, PurchaseRequest $purchase, PurchaseRequestService $purchases): RedirectResponse
    {
        $purchases->approve($purchase, $request->user(), $request->validated('note'));

        return redirect()->route('parent.purchases.index')
            ->with('success', "Approved {$purchase->item_name} for {$purchase->requester->name}. They can complete it when they buy it.");
    }

    public function reject(DecidePurchaseRequest $request, PurchaseRequest $purchase, PurchaseRequestService $purchases): RedirectResponse
    {
        $purchases->reject($purchase, $request->user(), $request->validated('note'));

        return redirect()->route('parent.purchases.index')
            ->with('success', "Rejected {$purchase->item_name} for {$purchase->requester->name}.");
    }
}