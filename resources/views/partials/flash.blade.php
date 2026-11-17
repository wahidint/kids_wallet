@foreach (['success' => 'check-circle-fill', 'error' => 'exclamation-triangle-fill'] as $type => $icon)
    @if (session($type))
        <div class="alert alert-{{ $type === 'error' ? 'danger' : 'success' }} alert-dismissible fade show d-flex align-items-center gap-2 rounded-4" role="alert">
            <i class="bi bi-{{ $icon }}"></i>
            <div>{{ session($type) }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
@endforeach

{{-- F10: "New badge unlocked!" celebration --}}
@if (session('new_badges'))
    @foreach (session('new_badges') as $badge)
        <div class="badge-unlocked alert alert-dismissible fade show rounded-4" role="alert">
            <div class="badge-medal badge-medal-sm" style="--badge-color: {{ $badge['color'] }};">
                <i class="bi {{ $badge['icon'] }}"></i>
            </div>
            <div class="flex-grow-1">
                <div class="fw-semibold">New badge unlocked: {{ $badge['label'] }}!</div>
                <div class="small opacity-75">Great money habits pay off. See all your badges in Rewards.</div>
            </div>
            <a href="{{ route('child.rewards') }}" class="btn btn-sm btn-kw rounded-pill px-3">View</a>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endforeach

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
        <script>
            confetti({ particleCount: 140, spread: 80, origin: { y: 0.3 } });
        </script>
    @endpush
@endif