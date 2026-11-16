{{-- One savings goal. Needs: $goal. Optional: $interactive = true for Save / Take out buttons. --}}
@php
    $percent = $goal->percent();
    $daysLeft = $goal->daysLeft();
    $weekly = $goal->weeklySuggestion();
    $hasMoney = bccomp($goal->current_amount, '0', 2) > 0;
@endphp

<div @class(['goal-card', 'is-achieved' => $goal->isAchieved()])>
    <div class="d-flex align-items-start gap-3">
        <div class="goal-icon"><i class="bi {{ $goal->icon }}"></i></div>
        <div class="flex-grow-1 min-w-0">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="fw-semibold">{{ $goal->title }}</span>
                @if ($goal->isAchieved())
                    <span class="badge rounded-pill text-bg-success"><i class="bi bi-trophy"></i> Achieved</span>
                @endif
            </div>
            <div class="small text-secondary">
                @if ($goal->isAchieved())
                    Reached on {{ $goal->achieved_at->format('j M Y') }}
                @elseif ($daysLeft > 0)
                    By {{ $goal->deadline->format('j M Y') }} · {{ $daysLeft }} {{ \Illuminate\Support\Str::plural('day', $daysLeft) }} left
                @elseif ($daysLeft === 0)
                    <span class="text-warning-emphasis fw-medium">Due today!</span>
                @else
                    <span class="text-danger">Deadline passed ({{ $goal->deadline->format('j M Y') }})</span>
                @endif
            </div>
        </div>
        <div class="text-end text-nowrap">
            <div class="fw-bold">৳{{ number_format($goal->current_amount, 2) }}</div>
            <div class="small text-secondary">of ৳{{ number_format($goal->target_amount, 2) }}</div>
        </div>
    </div>

    <div class="progress goal-progress mt-3" role="progressbar" aria-label="{{ $goal->title }} progress"
         aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">
        <div class="progress-bar" style="width: {{ $percent }}%"></div>
    </div>
    <div class="d-flex justify-content-between small mt-1 text-secondary">
        <span>{{ $percent }}% saved</span>
        @unless ($goal->isAchieved())
            <span>৳{{ number_format($goal->remaining(), 2) }} to go</span>
        @endunless
    </div>

    @if ($weekly)
        <div class="small text-kw mt-1">
            <i class="bi bi-lightbulb"></i> Save about ৳{{ number_format($weekly) }} a week to reach it on time.
        </div>
    @endif

    @if (! empty($interactive))
        @if (! $goal->isAchieved() || $hasMoney)
            <form method="POST" action="{{ route('child.goals.contribute', $goal) }}" class="goal-actions">
                @csrf
                <div class="input-group">
                    <span class="input-group-text">৳</span>
                    <input type="number" name="amount" step="0.01" min="1" class="form-control" placeholder="Amount" required>
                </div>
                <div class="d-flex gap-2">
                    @unless ($goal->isAchieved())
                        <button type="submit" class="btn btn-kw rounded-pill px-3">
                            <i class="bi bi-piggy-bank"></i> Save
                        </button>
                    @endunless
                    @if ($hasMoney)
                        <button type="submit" formaction="{{ route('child.goals.withdraw', $goal) }}" class="btn btn-light border rounded-pill px-3">
                            <i class="bi bi-box-arrow-up"></i> Take out
                        </button>
                    @endif
                </div>
            </form>
        @endif

        @unless ($hasMoney)
            <form method="POST" action="{{ route('child.goals.destroy', $goal) }}" class="mt-2 text-end"
                  onsubmit="return confirm({{ \Illuminate\Support\Js::from('Delete the goal "'.$goal->title.'"?') }});">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-link btn-sm text-secondary text-decoration-none p-0">
                    <i class="bi bi-trash"></i> Delete goal
                </button>
            </form>
        @endunless
    @endif
</div>