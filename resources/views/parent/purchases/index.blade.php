@extends('layouts.app')

@section('title', 'Purchase requests')

@section('content')
    <h2 class="h3 fw-bold mb-1">Purchase requests</h2>
    <p class="text-secondary mb-4">
        Your children ask before buying something special. Money is only taken after they complete an approved purchase.
    </p>

    @if ($errors->any())
        <div class="alert alert-danger rounded-4">{{ $errors->first() }}</div>
    @endif

    <h3 class="h6 fw-semibold mb-3">
        Waiting for you
        <span class="badge rounded-pill text-bg-warning">{{ $pending->count() }}</span>
    </h3>

    @forelse ($pending as $purchase)
        @php
            $child = $purchase->requester;
            $firstName = \Illuminate\Support\Str::before($child->name, ' ');
            $balance = $purchase->wallet->balance;
            $notEnough = bccomp($balance, $purchase->amount, 2) < 0;
        @endphp

        <div class="kw-card request-card mb-3">
            <div class="d-flex flex-wrap align-items-start gap-3">
                <div class="avatar avatar-lg">{{ mb_strtoupper(mb_substr($child->name, 0, 1)) }}</div>
                <div class="flex-grow-1 min-w-0">
                    <div class="small text-secondary">{{ $child->name }} asked {{ $purchase->created_at->diffForHumans() }}</div>
                    <div class="fs-5 fw-semibold">{{ $purchase->item_name }}</div>
                    <div class="small text-secondary">
                        <i class="bi {{ $purchase->category?->icon ?? 'bi-bag' }}" style="color: {{ $purchase->category?->color ?? '#6B7280' }};"></i>
                        {{ $purchase->category?->name ?? 'Other' }}
                    </div>
                    @if ($purchase->description)
                        <div class="small mt-2">“{{ $purchase->description }}”</div>
                    @endif
                </div>
                <div class="text-end">
                    <div class="fs-4 fw-bold">৳{{ number_format($purchase->amount, 2) }}</div>
                    <div class="small text-secondary">Balance: ৳{{ number_format($balance, 2) }}</div>
                </div>
            </div>

            @if ($notEnough)
                <div class="alert alert-warning small py-2 mt-3 mb-0 rounded-3">
                    <i class="bi bi-info-circle"></i>
                    {{ $firstName }} doesn't have enough yet. If you approve, they can complete it once they have
                    ৳{{ number_format($purchase->amount, 2) }}, or you can add money first.
                </div>
            @endif

            <form method="POST" action="{{ route('parent.purchases.approve', $purchase) }}" class="request-actions">
                @csrf
                <input type="text" name="note" class="form-control" maxlength="200"
                       placeholder="Add a note for {{ $firstName }} (optional)">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success rounded-pill px-4">
                        <i class="bi bi-check-lg"></i> Approve
                    </button>
                    <button type="submit" formaction="{{ route('parent.purchases.reject', $purchase) }}"
                            class="btn btn-outline-danger rounded-pill px-4">
                        <i class="bi bi-x-lg"></i> Reject
                    </button>
                </div>
            </form>
        </div>
    @empty
        <div class="kw-card mb-3">
            <div class="empty-state">
                <i class="bi bi-check2-all"></i>
                <p class="fw-semibold text-dark mt-2 mb-1">All caught up!</p>
                <p class="small mb-0">No purchase requests are waiting for you.</p>
            </div>
        </div>
    @endforelse

    <h3 class="h6 fw-semibold mt-4 mb-3">Recent decisions</h3>
    <div class="kw-card">
        @forelse ($history as $purchase)
            <div class="tx-row">
                <div class="avatar">{{ mb_strtoupper(mb_substr($purchase->requester->name, 0, 1)) }}</div>
                <div class="flex-grow-1 min-w-0">
                    <div class="fw-medium text-truncate">
                        {{ $purchase->item_name }}
                        <span class="text-secondary fw-normal">· {{ $purchase->requester->name }}</span>
                    </div>
                    <div class="small text-secondary text-truncate">
                        @if ($purchase->decidedBy)
                            Decided by {{ $purchase->decidedBy->name }} ·
                        @endif
                        {{ $purchase->updated_at->format('j M, g:i A') }}
                        @if ($purchase->parent_note)
                            · “{{ $purchase->parent_note }}”
                        @endif
                    </div>
                </div>
                <div class="text-end">
                    <div class="fw-semibold">৳{{ number_format($purchase->amount, 2) }}</div>
                    <span class="badge rounded-pill {{ $purchase->status->badgeClass() }}">{{ $purchase->status->label() }}</span>
                </div>
            </div>
        @empty
            <p class="text-secondary small mb-0">No decisions yet.</p>
        @endforelse
    </div>
@endsection