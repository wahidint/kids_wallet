@extends('layouts.app')

@section('title', $child->name.' · Allowances')

@section('content')
    @php $firstName = \Illuminate\Support\Str::before($child->name, ' '); @endphp

    <a href="{{ route('parent.children.show', $child) }}" class="text-secondary text-decoration-none small">
        <i class="bi bi-arrow-left"></i> Back to {{ $firstName }}'s wallet
    </a>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-3 mb-4">
        <div>
            <h2 class="h3 fw-bold mb-1">{{ $firstName }}'s allowances</h2>
            <p class="text-secondary mb-0">Scheduled pocket money is paid automatically on the due day.</p>
        </div>
        <a href="{{ route('parent.children.allowances.create', $child) }}" class="btn btn-kw rounded-pill px-4">
            <i class="bi bi-plus-lg"></i> New schedule
        </a>
    </div>

    @forelse ($schedules as $schedule)
        @php $daysUntil = (int) now()->startOfDay()->diffInDays($schedule->next_run_at, false); @endphp

        <div @class(['kw-card schedule-card mb-3', 'is-paused' => ! $schedule->is_active])>
            <div class="d-flex flex-wrap align-items-center gap-3">
                <div class="schedule-icon"><i class="bi bi-calendar-heart"></i></div>
                <div class="flex-grow-1 min-w-0">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="fs-5 fw-bold">৳{{ number_format($schedule->amount, 2) }}</span>
                        <span class="fw-medium">{{ $schedule->scheduleLabel() }}</span>
                        @if ($schedule->is_active)
                            <span class="badge rounded-pill text-bg-success">Active</span>
                        @else
                            <span class="badge rounded-pill text-bg-secondary">Paused</span>
                        @endif
                    </div>
                    <div class="small text-secondary">
                        {{ $schedule->description() }}
                        @if ($schedule->is_active)
                            · Next payment: <strong>{{ $schedule->next_run_at->format('D, j M Y') }}</strong>
                            ({{ $daysUntil <= 0 ? 'today' : 'in '.$daysUntil.' '.\Illuminate\Support\Str::plural('day', $daysUntil) }})
                        @endif
                        @if ($schedule->last_run_at)
                            · Last paid {{ $schedule->last_run_at->format('j M') }}
                        @endif
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('parent.children.allowances.edit', [$child, $schedule]) }}" class="btn btn-sm btn-light border rounded-pill px-3">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <form method="POST" action="{{ route('parent.children.allowances.toggle', [$child, $schedule]) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-light border rounded-pill px-3">
                            <i class="bi {{ $schedule->is_active ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                            {{ $schedule->is_active ? 'Pause' : 'Resume' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('parent.children.allowances.destroy', [$child, $schedule]) }}"
                          onsubmit="return confirm('Delete this allowance schedule? Past payments stay in the history.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="kw-card mb-3">
            <div class="empty-state">
                <i class="bi bi-calendar-plus"></i>
                <p class="fw-semibold text-dark mt-2 mb-1">No allowance scheduled</p>
                <p class="small mb-3">Set up weekly or monthly pocket money and it will be paid automatically.</p>
                <a href="{{ route('parent.children.allowances.create', $child) }}" class="btn btn-kw rounded-pill px-4">Schedule an allowance</a>
            </div>
        </div>
    @endforelse

    <div class="kw-card mt-4">
        <h3 class="h6 fw-semibold mb-2">Allowance history</h3>
        @forelse ($history as $transaction)
            @include('partials.transaction-row')
        @empty
            <p class="text-secondary small mb-0">No allowances have been paid yet.</p>
        @endforelse
    </div>
@endsection