<?php

namespace App\Services;

use App\Enums\TransactionSource;
use App\Models\AllowancePayment;
use App\Models\AllowanceSchedule;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Feature 7: Allowance Scheduler.
 */
class AllowanceService
{
    public function __construct(
        private WalletService $wallets,
    ) {
    }

    public function create(User $child, User $parent, array $data): AllowanceSchedule
    {
        $schedule = new AllowanceSchedule($this->clean($data));
        $schedule->wallet()->associate($child->wallet);
        $schedule->creator()->associate($parent);
        $schedule->next_run_at = $schedule->firstDateFrom(now());
        $schedule->save();

        return $schedule;
    }

    public function update(AllowanceSchedule $schedule, array $data): void
    {
        $schedule->fill($this->clean($data));
        $schedule->next_run_at = $schedule->firstDateFrom(now());
        $schedule->save();
    }

    /** Pause or resume. Resuming continues from the next due date (no catch-up payments). */
    public function toggle(AllowanceSchedule $schedule): void
    {
        $schedule->is_active = ! $schedule->is_active;

        if ($schedule->is_active) {
            $schedule->next_run_at = $schedule->firstDateFrom(now());
        }

        $schedule->save();
    }

    /**
     * Pay every schedule that is due. Safe to run any number of times.
     * Returns how many payments were made.
     */
    public function processDue(): int
    {
        $today = now()->startOfDay();

        return AllowanceSchedule::where('is_active', true)
            ->whereDate('next_run_at', '<=', $today)
            ->pluck('id')
            ->sum(fn (int $id) => $this->processSchedule($id, $today));
    }

    private function processSchedule(int $id, Carbon $today): int
    {
        return DB::transaction(function () use ($id, $today) {
            // Lock the schedule so two runs at the same moment can't both pay it
            $schedule = AllowanceSchedule::with(['wallet', 'creator'])->lockForUpdate()->find($id);

            if (! $schedule || ! $schedule->is_active) {
                return 0;
            }

            $paid = 0;
            $safety = 0;

            // Pays every missed due date too (e.g. the laptop was off on Friday), max 12
            while ($schedule->next_run_at->lte($today) && $safety++ < 12) {
                $due = $schedule->next_run_at->copy();

                $alreadyPaid = AllowancePayment::where('allowance_schedule_id', $schedule->id)
                    ->whereDate('due_on', $due)
                    ->exists();

                if (! $alreadyPaid) {
                    $transaction = $this->wallets->credit(
                        $schedule->wallet,
                        $schedule->amount,
                        TransactionSource::Allowance,
                        $schedule->description(),
                        $schedule->creator,
                        $schedule,
                    );

                    $schedule->payments()->create([
                        'due_on' => $due->toDateString(),
                        'transaction_id' => $transaction->id,
                    ]);

                    $paid++;
                }

                $schedule->forceFill([
                    'next_run_at' => $schedule->followingDate($due),
                    'last_run_at' => now(),
                ])->save();
            }

            return $paid;
        });
    }

    /** Keep only the day field that matches the frequency. */
    private function clean(array $data): array
    {
        $weekly = $data['frequency'] === AllowanceSchedule::WEEKLY;

        return [
            'amount' => $data['amount'],
            'frequency' => $data['frequency'],
            'day_of_week' => $weekly ? (int) $data['day_of_week'] : null,
            'day_of_month' => $weekly ? null : (int) $data['day_of_month'],
            'note' => $data['note'] ?? null,
        ];
    }
}