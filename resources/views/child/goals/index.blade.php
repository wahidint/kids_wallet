@extends('layouts.app')

@section('title', 'Savings goals')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="h3 fw-bold mb-1">My savings goals</h2>
            <p class="text-secondary mb-0">Save a little at a time and watch your goals grow.</p>
        </div>
        <a href="{{ route('child.goals.create') }}" class="btn btn-kw rounded-pill px-4">
            <i class="bi bi-plus-lg"></i> New goal
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger rounded-4">{{ $errors->first() }}</div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-sm-6">
            <div class="kw-card stat-card d-flex align-items-center gap-3">
                <div class="stat-icon"><i class="bi bi-piggy-bank"></i></div>
                <div>
                    <div class="stat-label">Total saved</div>
                    <div class="stat-value">৳{{ number_format($totalSaved, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="kw-card stat-card d-flex align-items-center gap-3">
                <div class="stat-icon"><i class="bi bi-wallet2"></i></div>
                <div>
                    <div class="stat-label">Available to save</div>
                    <div class="stat-value">৳{{ number_format($wallet->balance, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    @if ($goals->isEmpty())
        <div class="kw-card">
            <div class="empty-state">
                <i class="bi bi-piggy-bank"></i>
                <p class="fw-semibold text-dark mt-2 mb-1">No goals yet</p>
                <p class="small mb-3">What would you love to save for? A bicycle, a game, a trip?</p>
                <a href="{{ route('child.goals.create') }}" class="btn btn-kw rounded-pill px-4">Create my first goal</a>
            </div>
        </div>
    @else
        <div class="row g-3">
            @foreach ($goals as $goal)
                <div class="col-lg-6">
                    @include('partials.goal-card', ['goal' => $goal, 'interactive' => true])
                </div>
            @endforeach
        </div>
    @endif

    {{-- Celebration when a goal is reached --}}
    @if (session('goal_achieved'))
        <div class="modal fade" id="achievedModal" tabindex="-1" aria-labelledby="achievedTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 text-center p-4">
                    <div class="celebrate-icon mx-auto mb-3"><i class="bi bi-trophy-fill"></i></div>
                    <h3 class="h4 fw-bold" id="achievedTitle">Goal reached!</h3>
                    <p class="text-secondary mb-4">
                        You saved enough for <strong>{{ session('goal_achieved') }}</strong>. Amazing work, keep it up!
                    </p>
                    <button type="button" class="btn btn-kw btn-lg rounded-pill" data-bs-dismiss="modal">Yay! 🎉</button>
                </div>
            </div>
        </div>

        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
            <script>
                new bootstrap.Modal(document.getElementById('achievedModal')).show();
                confetti({ particleCount: 160, spread: 90, origin: { y: 0.6 } });
            </script>
        @endpush
    @endif
@endsection