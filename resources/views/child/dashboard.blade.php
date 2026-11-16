@extends('layouts.app')

@section('title', 'My Wallet')

@section('content')
    @php $usage = $wallet->limitUsage(); @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="h3 fw-bold mb-1">Hi {{ \Illuminate\Support\Str::before($child->name, ' ') }}! 👋</h2>
            <p class="text-secondary mb-0">Every taka you save today is a step toward your goals.</p>
        </div>
        <a href="{{ route('child.spend.create') }}" class="btn btn-kw rounded-pill px-4">
            <i class="bi bi-bag-check"></i> Spend money
        </a>
    </div>

    @if ($wallet->is_locked)
        <div class="alert alert-warning d-flex gap-3 align-items-start rounded-4">
            <i class="bi bi-lock-fill fs-4"></i>
            <div>
                <div class="fw-semibold">Your wallet is locked</div>
                <div class="small">
                    {{ $wallet->lockedBy?->name ?? 'A parent' }} paused spending{{ $wallet->locked_at ? ' on '.$wallet->locked_at->format('j M Y') : '' }}.
                    @if ($wallet->lock_reason)
                        Reason: “{{ $wallet->lock_reason }}”.
                    @endif
                    You can still see your balance and history.
                </div>
            </div>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="kw-card balance-card p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="small opacity-75">Available balance</div>
                        <div class="balance-amount">৳{{ number_format($wallet->balance, 2) }}</div>
                    </div>
                    <span class="badge rounded-pill bg-white text-dark px-3 py-2">
                        <i class="bi {{ $wallet->is_locked ? 'bi-lock-fill text-danger' : 'bi-shield-check text-success' }}"></i>
                        {{ $wallet->is_locked ? 'Locked' : 'Active' }}
                    </span>
                </div>
                <div class="row g-3 mt-3">
                    <div class="col-6">
                        <div class="balance-mini">
                            <div class="small opacity-75"><i class="bi bi-arrow-down-left"></i> Received this month</div>
                            <div class="fw-semibold fs-5">৳{{ number_format($monthIncome, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="balance-mini">
                            <div class="small opacity-75"><i class="bi bi-arrow-up-right"></i> Spent this month</div>
                            <div class="fw-semibold fs-5">৳{{ number_format($monthSpending, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="kw-card h-100">
                @if (! empty($usage))
                    <h3 class="h6 fw-semibold mb-3"><i class="bi bi-speedometer2 text-kw"></i> My spending limits</h3>
                    @include('partials.limit-usage', ['usage' => $usage])
                    <hr class="my-3">
                @endif

                <h3 class="h6 fw-semibold mb-3"><i class="bi bi-people-fill text-kw"></i> My family</h3>
                @forelse ($parents as $parent)
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="avatar">{{ mb_strtoupper(mb_substr($parent->name, 0, 1)) }}</div>
                        <div>
                            <div class="fw-semibold">{{ $parent->name }}</div>
                            <div class="small text-secondary">Parent</div>
                        </div>
                    </div>
                @empty
                    <p class="text-secondary mb-0">You're not linked to a parent yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="kw-card">
        <h3 class="h6 fw-semibold mb-2">Recent transactions</h3>
        @forelse ($recentTransactions as $transaction)
            @include('partials.transaction-row')
        @empty
            <div class="empty-state">
                <i class="bi bi-receipt"></i>
                <p class="mt-2 mb-0">No transactions yet. Your history will appear here.</p>
            </div>
        @endforelse
    </div>
@endsection