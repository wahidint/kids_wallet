<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Http\Requests\Parent\SpendingLimitRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Feature 2: parents set daily / weekly / monthly spending limits.
 */
class SpendingLimitController extends Controller
{
    public function edit(User $child): View
    {
        $wallet = $child->wallet->load('spendingLimit');

        return view('parent.limits.edit', [
            'child' => $child,
            'wallet' => $wallet,
            'limit' => $wallet->spendingLimit,
        ]);
    }

    public function update(SpendingLimitRequest $request, User $child): RedirectResponse
    {
        $data = $request->validated();

        $values = [
            'daily_limit' => $data['daily_limit'] ?? null,
            'weekly_limit' => $data['weekly_limit'] ?? null,
            'monthly_limit' => $data['monthly_limit'] ?? null,
        ];

        // Create the limits row the first time, update it after that
        $child->wallet->spendingLimit()->updateOrCreate([], $values + [
            'updated_by' => $request->user()->id,
        ]);

        $message = collect($values)->filter()->isEmpty()
            ? "All spending limits removed for {$child->name}."
            : "Spending limits saved for {$child->name}.";

        return redirect()->route('parent.children.show', $child)->with('success', $message);
    }
}