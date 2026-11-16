<?php

namespace App\Http\Controllers\Child;

use App\Http\Controllers\Controller;
use App\Http\Requests\Child\SavingsAmountRequest;
use App\Http\Requests\Child\StoreSavingsGoalRequest;
use App\Models\SavingsGoal;
use App\Services\SavingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SavingsGoalController extends Controller
{
    public function index(Request $request): View
    {
        $child = $request->user();

        $goals = SavingsGoal::where('user_id', $child->id)
            ->orderByRaw('achieved_at IS NOT NULL') // active goals first
            ->orderBy('deadline')
            ->get();

        return view('child.goals.index', [
            'goals' => $goals,
            'wallet' => $child->wallet,
            'totalSaved' => $goals->reduce(fn (string $sum, SavingsGoal $goal) => bcadd($sum, $goal->current_amount, 2), '0.00'),
        ]);
    }

    public function create(): View
    {
        return view('child.goals.create', ['icons' => SavingsGoal::ICONS]);
    }

    public function store(StoreSavingsGoalRequest $request, SavingsService $savings): RedirectResponse
    {
        $goal = $savings->create($request->user(), $request->validated());

        return redirect()->route('child.goals.index')
            ->with('success', "Goal created! Start saving for {$goal->title}.");
    }

    public function contribute(SavingsAmountRequest $request, SavingsGoal $goal, SavingsService $savings): RedirectResponse
    {
        $amount = $request->validated('amount');
        $achieved = $savings->contribute($goal, $request->user(), $amount);

        $redirect = redirect()->route('child.goals.index')
            ->with('success', '৳'.number_format($amount, 2)." saved for {$goal->title}.");

        return $achieved ? $redirect->with('goal_achieved', $goal->title) : $redirect;
    }

    public function withdraw(SavingsAmountRequest $request, SavingsGoal $goal, SavingsService $savings): RedirectResponse
    {
        $amount = $request->validated('amount');
        $savings->withdraw($goal, $request->user(), $amount);

        return redirect()->route('child.goals.index')
            ->with('success', '৳'.number_format($amount, 2)." moved from {$goal->title} back to your wallet.");
    }

    public function destroy(SavingsGoal $goal, SavingsService $savings): RedirectResponse
    {
        $savings->delete($goal);

        return redirect()->route('child.goals.index')
            ->with('success', "Goal \"{$goal->title}\" deleted.");
    }
}