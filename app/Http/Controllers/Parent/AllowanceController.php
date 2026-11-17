<?php

namespace App\Http\Controllers\Parent;

use App\Enums\TransactionSource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Parent\AllowanceScheduleRequest;
use App\Models\AllowanceSchedule;
use App\Models\User;
use App\Services\AllowanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Feature 7. The route's "can:manage-child" check runs before every method.
 */
class AllowanceController extends Controller
{
    public function index(User $child): View
    {
        $wallet = $child->wallet;

        return view('parent.allowances.index', [
            'child' => $child,
            'schedules' => AllowanceSchedule::where('wallet_id', $wallet->id)
                ->orderByDesc('is_active')
                ->orderBy('next_run_at')
                ->get(),
            'history' => $wallet->transactions()
                ->where('source', TransactionSource::Allowance->value)
                ->latest()
                ->latest('id')
                ->take(15)
                ->get(),
        ]);
    }

    public function create(User $child): View
    {
        return view('parent.allowances.form', ['child' => $child, 'schedule' => null]);
    }

    public function store(AllowanceScheduleRequest $request, User $child, AllowanceService $allowances): RedirectResponse
    {
        $schedule = $allowances->create($child, $request->user(), $request->validated());

        return redirect()->route('parent.children.allowances.index', $child)
            ->with('success', "Allowance set: ৳".number_format($schedule->amount, 2).' '.strtolower($schedule->scheduleLabel())
                .'. First payment on '.$schedule->next_run_at->format('D, j M').'.');
    }

    public function edit(User $child, AllowanceSchedule $schedule): View
    {
        $this->ensureBelongsTo($child, $schedule);

        return view('parent.allowances.form', ['child' => $child, 'schedule' => $schedule]);
    }

    public function update(AllowanceScheduleRequest $request, User $child, AllowanceSchedule $schedule, AllowanceService $allowances): RedirectResponse
    {
        $this->ensureBelongsTo($child, $schedule);
        $allowances->update($schedule, $request->validated());

        return redirect()->route('parent.children.allowances.index', $child)
            ->with('success', 'Allowance updated. Next payment on '.$schedule->next_run_at->format('D, j M').'.');
    }

    public function toggle(User $child, AllowanceSchedule $schedule, AllowanceService $allowances): RedirectResponse
    {
        $this->ensureBelongsTo($child, $schedule);
        $allowances->toggle($schedule);

        return redirect()->route('parent.children.allowances.index', $child)
            ->with('success', $schedule->is_active ? 'Allowance resumed.' : 'Allowance paused. No payments until you resume it.');
    }

    public function destroy(User $child, AllowanceSchedule $schedule): RedirectResponse
    {
        $this->ensureBelongsTo($child, $schedule);
        $schedule->delete();

        return redirect()->route('parent.children.allowances.index', $child)
            ->with('success', 'Allowance schedule deleted. Past payments stay in the history.');
    }

    /** A schedule from another child's URL is treated as "not found". */
    private function ensureBelongsTo(User $child, AllowanceSchedule $schedule): void
    {
        abort_unless($schedule->wallet_id === $child->wallet->id, 404);
    }
}