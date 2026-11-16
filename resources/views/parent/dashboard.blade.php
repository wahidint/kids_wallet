@extends('layouts.app')

@section('title', 'Parent Dashboard')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="h3 fw-bold mb-1">Hello, {{ $parent->name }}</h2>
            <p class="text-secondary mb-0">Here's how your family's wallets are doing.</p>
        </div>
        <div class="link-code-chip">
            <span class="small text-secondary">Family link code</span>
            <code id="linkCode" class="fs-5 fw-bold text-kw">{{ $parent->link_code }}</code>
            <button type="button" class="btn btn-sm btn-light" id="copyLinkCode" title="Copy code">
                <i class="bi bi-clipboard"></i>
            </button>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="kw-card stat-card d-flex align-items-center gap-3">
                <div class="stat-icon"><i class="bi bi-wallet2"></i></div>
                <div>
                    <div class="stat-label">Total in wallets</div>
                    <div class="stat-value">৳{{ number_format($totalBalance, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="kw-card stat-card d-flex align-items-center gap-3">
                <div class="stat-icon"><i class="bi bi-people"></i></div>
                <div>
                    <div class="stat-label">Children linked</div>
                    <div class="stat-value">{{ $children->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="kw-card stat-card d-flex align-items-center gap-3">
                <div class="stat-icon"><i class="bi bi-graph-down-arrow"></i></div>
                <div>
                    <div class="stat-label">Spent this month</div>
                    <div class="stat-value">৳{{ number_format($monthSpending, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-7">
            <div class="kw-card h-100">
                <h3 class="h6 fw-semibold mb-2">Your children</h3>

                @forelse ($children as $child)
                    <div class="d-flex flex-wrap align-items-center gap-3 py-3 border-bottom child-row">
                        <div class="avatar avatar-lg">{{ mb_strtoupper(mb_substr($child->name, 0, 1)) }}</div>
                        <div class="flex-grow-1 min-w-0">
                            <a href="{{ route('parent.children.show', $child) }}" class="fw-semibold child-name-link">
                                {{ $child->name }}
                            </a>
                            <div class="small text-secondary">
                                @if ($child->date_of_birth)
                                    Age {{ $child->date_of_birth->age }} ·
                                @endif
                                @if ($child->wallet?->is_locked)
                                    <span class="text-danger"><i class="bi bi-lock-fill"></i> Locked</span>
                                @else
                                    <span class="text-success"><i class="bi bi-unlock"></i> Active</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-end me-2">
                            <div class="small text-secondary">Balance</div>
                            <div class="fw-bold">৳{{ number_format($child->wallet?->balance ?? 0, 2) }}</div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('parent.children.pocket-money.create', $child) }}" class="btn btn-sm btn-kw rounded-pill" title="Add money">
                                <i class="bi bi-plus-lg"></i><span class="d-none d-sm-inline"> Add money</span>
                            </a>
                            <a href="{{ route('parent.children.show', $child) }}" class="btn btn-sm btn-light border rounded-pill" title="View wallet">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <i class="bi bi-person-plus"></i>
                        <p class="fw-semibold text-dark mt-2 mb-1">No children linked yet</p>
                        <p class="small mb-0">Share your family link code above. When your child registers with it, their wallet appears here.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="col-xl-5">
            <div class="kw-card h-100">
                <h3 class="h6 fw-semibold mb-2">Recent activity</h3>

                @forelse ($recentTransactions as $transaction)
                    @include('partials.transaction-row', ['showChild' => true])
                @empty
                    <p class="text-secondary mb-0">No transactions yet.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const copyButton = document.getElementById('copyLinkCode');

        copyButton?.addEventListener('click', async () => {
            const code = document.getElementById('linkCode').textContent.trim();
            try {
                await navigator.clipboard.writeText(code);
                copyButton.innerHTML = '<i class="bi bi-check2"></i>';
                setTimeout(() => copyButton.innerHTML = '<i class="bi bi-clipboard"></i>', 1500);
            } catch {
                alert('Your family link code is: ' + code);
            }
        });
    </script>
@endpush