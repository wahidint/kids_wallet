@extends('layouts.app')

@section('title', 'Rewards')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="h3 fw-bold mb-1">My rewards</h2>
            <p class="text-secondary mb-0">Earn badges by building great money habits.</p>
        </div>
        <div class="kw-card py-2 px-3 text-center">
            <div class="stat-label">Badges earned</div>
            <div class="stat-value">{{ $earnedCount }} / {{ count($progress) }}</div>
        </div>
    </div>

    <div class="row g-3">
        @foreach ($progress as $row)
            @php $badge = $row['badge']; @endphp
            <div class="col-md-6 col-xl-4">
                <div @class(['kw-card reward-card h-100 text-center', 'is-locked' => ! $row['earned']])>
                    <div class="badge-medal mx-auto" style="--badge-color: {{ $badge->color() }};">
                        <i class="bi {{ $row['earned'] ? $badge->icon() : 'bi-lock-fill' }}"></i>
                    </div>
                    <h3 class="h5 fw-bold mt-3 mb-1">{{ $badge->label() }}</h3>
                    <p class="small text-secondary mb-3">{{ $badge->description() }}</p>

                    @if ($row['earned'])
                        <span class="badge rounded-pill text-bg-success px-3 py-2">
                            <i class="bi bi-check2-circle"></i> Earned {{ $row['earned']->earned_at->format('j M Y') }}
                        </span>
                    @else
                        <div class="progress limit-progress mb-1" role="progressbar" aria-label="{{ $badge->label() }} progress"
                             aria-valuenow="{{ $row['percent'] }}" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar" style="width: {{ $row['percent'] }}%; background: {{ $badge->color() }};"></div>
                        </div>
                        <div class="small text-secondary">{{ $badge->progressLabel($row['current']) }}</div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="tip-card mt-4">
        <i class="bi bi-lightbulb"></i>
        <div><span class="fw-semibold">Tip:</span> Saving a little every week is the fastest way to unlock Consistent Saver and Super Saver!</div>
    </div>
@endsection