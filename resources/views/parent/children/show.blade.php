@extends('layouts.app')

@section('title', $child->name)

@section('content')
    @php
        $firstName = \Illuminate\Support\Str::before($child->name, ' ');
        $usage = $wallet->limitUsage();
    @endphp

    <a href="{{ route('parent.dashboard') }}" class="text-secondary text-decoration-none small">
        <i class="bi bi-arrow-left"></i> Back to dashboard
    </a>

    <div class="d-flex flex-wrap align-items-center gap-3 mt-3 mb-4">
        <div class="avatar avatar-xl">{{ mb_strtoupper(mb_substr($child->name, 0, 1)) }}</div>
        <div class="me-auto">
            <h2 class="h3 fw-bold mb-1">{{ $child->name }}</h2>
            <div class="text-secondary small">
                @if ($child->date_of_birth)
                    Age {{ $child->date_of_birth->age }} ·
                @endif
                {{ $child->email }}
            </div>
        </div>
        <a href="{{ route('parent.children.insights', $child) }}" class="btn btn-light border rounded-pill px-4">
            <i class="bi bi-pie-chart"></i> Insights
        </a>
        <a href="{{ route('parent.children.pocket-money.create', $child) }}" class="btn btn-kw rounded-pill px-4">
            <i class="bi bi-plus-lg"></i> Add money
        </a>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <div class="kw-card balance-card h-100 p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="small opacity-75">Current balance</div>
                    <span class="badge rounded-pill bg-white text-dark px-3 py-2">
                        <i class="bi {{ $wallet->is_locked ? 'bi-lock-fill text-danger' : 'bi-shield-check text-success' }}"></i>
                        {{ $wallet->is_locked ? 'Locked' : 'Active' }}
                    </span>
                </div>
                <div class="balance-amount mt-2">৳{{ number_format($wallet->balance, 2) }}</div>
                <div class="small opacity-75 mt-1"><i class="bi bi-piggy-bank"></i> Plus ৳{{ number_format($totalSaved, 2) }} in savings goals</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="kw-card stat-card h-100 d-flex align-items-center gap-3">
                <div class="stat-icon text-success" style="background:#DCFCE7;"><i class="bi bi-arrow-down-left"></i></div>
                <div>
                    <div class="stat-label">Received this month</div>
                    <div class="stat-value">৳{{ number_format($monthIncome, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="kw-card stat-card h-100 d-flex align-items-center gap-3">
                <div class="stat-icon text-danger" style="background:#FEE2E2;"><i class="bi bi-arrow-up-right"></i></div>
                <div>
                    <div class="stat-label">Spent this month</div>
                    <div class="stat-value">৳{{ number_format($monthSpending, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        {{-- F2: Smart Spending Limits --}}
        <div class="col-lg-6">
            <div class="kw-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="fw-semibold"><i class="bi bi-speedometer2 text-kw"></i> Spending limits</div>
                    <a href="{{ route('parent.children.limits.edit', $child) }}" class="btn btn-sm btn-light border rounded-pill px-3">
                        <i class="bi bi-pencil"></i> {{ empty($usage) ? 'Set limits' : 'Edit' }}
                    </a>
                </div>

                @if (empty($usage))
                    <p class="small text-secondary mb-0">
                        No limits set. Daily, weekly or monthly limits help {{ $firstName }} learn to budget.
                    </p>
                @else
                    @include('partials.limit-usage', ['usage' => $usage])
                @endif
            </div>
        </div>

        {{-- F9: Emergency Wallet Lock --}}
        <div class="col-lg-6">
            <div @class(['kw-card lock-card h-100 d-flex flex-wrap align-items-center gap-3', 'is-locked' => $wallet->is_locked])>
                <div class="lock-icon">
                    <i class="bi {{ $wallet->is_locked ? 'bi-lock-fill' : 'bi-unlock' }}"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="fw-semibold">Emergency wallet lock</div>
                    @if ($wallet->is_locked)
                        <div class="small">
                            <span class="text-danger fw-semibold">Locked</span>
                            by {{ $wallet->lockedBy?->name ?? 'a parent' }}{{ $wallet->locked_at ? ' on '.$wallet->locked_at->format('j M Y, g:i A') : '' }}.
                            @if ($wallet->lock_reason)
                                Reason: “{{ $wallet->lock_reason }}”.
                            @endif
                            Spending is paused; you can still add money.
                        </div>
                    @else
                        <div class="small text-secondary">
                            <span class="text-success fw-semibold">Active.</span>
                            Lost phone or something looks wrong? Lock the wallet to stop all spending instantly.
                        </div>
                    @endif
                </div>

                @if ($wallet->is_locked)
                    <form method="POST" action="{{ route('parent.children.unlock', $child) }}"
                          onsubmit="return confirm('Unlock {{ $firstName }}\'s wallet? Spending will be allowed again.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-success rounded-pill px-4">
                            <i class="bi bi-unlock"></i> Unlock wallet
                        </button>
                    </form>
                @else
                    <button type="button" class="btn btn-outline-danger rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#lockModal">
                        <i class="bi bi-lock"></i> Lock wallet
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- F3: Savings goals (view only) --}}
    <div class="kw-card mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="fw-semibold"><i class="bi bi-piggy-bank text-kw"></i> {{ $firstName }}'s savings goals</div>
            <span class="small text-secondary">৳{{ number_format($totalSaved, 2) }} saved</span>
        </div>
        @if ($goals->isEmpty())
            <p class="small text-secondary mb-0">{{ $firstName }} hasn't created any savings goals yet.</p>
        @else
            <div class="row g-3">
                @foreach ($goals as $goal)
                    <div class="col-lg-6">@include('partials.goal-card', ['goal' => $goal])</div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="kw-card">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h3 class="h6 fw-semibold mb-0">Transaction history</h3>
            <a href="{{ route('parent.children.transactions.index', $child) }}" class="small text-kw text-decoration-none">
                Filter &amp; export ({{ $transactions->total() }}) <i class="bi bi-chevron-right"></i>
            </a>
        </div>

        @forelse ($transactions as $transaction)
            @include('partials.transaction-row')
        @empty
            <div class="empty-state">
                <i class="bi bi-receipt"></i>
                <p class="mt-2 mb-0">No transactions yet. Add some pocket money to get started.</p>
            </div>
        @endforelse

        @if ($transactions->hasPages())
            <div class="mt-3">{{ $transactions->links() }}</div>
        @endif
    </div>

    {{-- Lock confirmation popup --}}
    @unless ($wallet->is_locked)
        <div class="modal fade" id="lockModal" tabindex="-1" aria-labelledby="lockModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="POST" action="{{ route('parent.children.lock', $child) }}" class="modal-content rounded-4 border-0" id="lockForm">
                    @csrf
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold" id="lockModalLabel">
                            <i class="bi bi-lock-fill text-danger"></i> Lock {{ $firstName }}'s wallet?
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-secondary">
                            All spending stops immediately. {{ $firstName }} can still log in and see the balance and history,
                            and you can still add money.
                        </p>
                        <div class="small fw-medium mb-2">Quick reason</div>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            @foreach (['Lost phone', 'Too much spending', 'Suspicious activity', 'Taking a short break'] as $suggestion)
                                <button type="button" class="btn btn-sm btn-light border rounded-pill reason-chip">{{ $suggestion }}</button>
                            @endforeach
                        </div>
                        <x-form-input name="reason" :label="'Reason (shown to '.$firstName.')'" maxlength="150" placeholder="Optional" />
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger rounded-pill px-4" id="lockSubmit">
                            <i class="bi bi-lock-fill"></i> Lock wallet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endunless
@endsection

@push('scripts')
    <script>
        const reasonInput = document.getElementById('reason');

        document.querySelectorAll('.reason-chip').forEach((chip) => {
            chip.addEventListener('click', () => {
                reasonInput.value = chip.textContent.trim();
                reasonInput.focus();
            });
        });

        document.getElementById('lockForm')?.addEventListener('submit', () => {
            const button = document.getElementById('lockSubmit');
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Locking...';
        });

        @if ($errors->has('reason'))
            // Re-open the popup so the error message is visible
            new bootstrap.Modal(document.getElementById('lockModal')).show();
        @endif
    </script>
@endpush