@extends('layouts.app')

@section('title', 'Ask to buy')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="h3 fw-bold mb-1">My purchase requests</h2>
            <p class="text-secondary mb-0">Want something special? Ask your parents first.</p>
        </div>
        <a href="{{ route('child.purchases.create') }}" class="btn btn-kw rounded-pill px-4">
            <i class="bi bi-plus-lg"></i> New request
        </a>
    </div>

    @forelse ($requests as $purchase)
        @php
            $status = $purchase->status;
            $color = $purchase->category?->color ?? '#6B7280';
        @endphp

        <div class="kw-card request-card mb-3">
            <div class="d-flex flex-wrap align-items-start gap-3">
                <div class="tx-icon" style="background: {{ $color }}1A; color: {{ $color }};">
                    <i class="bi {{ $purchase->category?->icon ?? 'bi-bag' }}"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="fw-semibold">{{ $purchase->item_name }}</span>
                        <span class="badge rounded-pill {{ $status->badgeClass() }}">
                            <i class="bi {{ $status->icon() }}"></i> {{ $status->label() }}
                        </span>
                    </div>
                    <div class="small text-secondary">
                        {{ $purchase->category?->name ?? 'Other' }} · Asked {{ $purchase->created_at->format('j M, g:i A') }}
                    </div>
                    @if ($purchase->description)
                        <div class="small mt-2">“{{ $purchase->description }}”</div>
                    @endif
                    @if ($purchase->parent_note)
                        <div class="parent-note mt-2 small">
                            <i class="bi bi-chat-quote"></i>
                            <strong>{{ $purchase->decidedBy?->name ?? 'Parent' }}:</strong> {{ $purchase->parent_note }}
                        </div>
                    @endif
                </div>
                <div class="fs-5 fw-bold text-nowrap">৳{{ number_format($purchase->amount, 2) }}</div>
            </div>

            @if ($purchase->isApproved())
                <div class="request-actions">
                    <div class="small text-secondary">
                        Approved! Bought it? ৳{{ number_format($purchase->amount, 2) }} will be paid from your wallet
                        (balance ৳{{ number_format($wallet->balance, 2) }}).
                    </div>
                    <form method="POST" action="{{ route('child.purchases.complete', $purchase) }}"
                          onsubmit="return confirm({{ \Illuminate\Support\Js::from('Pay ৳'.number_format($purchase->amount, 2).' for '.$purchase->item_name.'?') }});">
                        @csrf
                        <button type="submit" class="btn btn-success rounded-pill px-4">
                            <i class="bi bi-bag-check"></i> I bought it
                        </button>
                    </form>
                </div>
            @elseif ($purchase->isPending())
                <div class="request-actions">
                    <div class="small text-secondary"><i class="bi bi-hourglass-split"></i> Waiting for a parent to decide.</div>
                    <form method="POST" action="{{ route('child.purchases.cancel', $purchase) }}">
                        @csrf
                        <button type="submit" class="btn btn-light border rounded-pill px-3">Cancel request</button>
                    </form>
                </div>
            @elseif ($purchase->isCompleted())
                <div class="small text-success mt-3">
                    <i class="bi bi-check2-circle"></i> Paid on {{ $purchase->completed_at->format('j M Y, g:i A') }}
                </div>
            @endif
        </div>
    @empty
        <div class="kw-card">
            <div class="empty-state">
                <i class="bi bi-patch-question"></i>
                <p class="fw-semibold text-dark mt-2 mb-1">No requests yet</p>
                <p class="small mb-0">Saving up for something special? Ask your parents with a new request.</p>
            </div>
        </div>
    @endforelse
@endsection