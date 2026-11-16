@extends('layouts.app')

@section('title', $child->name)

@section('content')
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
        <a href="{{ route('parent.children.pocket-money.create', $child) }}" class="btn btn-kw rounded-pill px-4">
            <i class="bi bi-plus-lg"></i> Add money
        </a>
    </div>

    @if ($wallet->is_locked)
        <div class="alert alert-warning d-flex gap-3 align-items-start rounded-4">
            <i class="bi bi-lock-fill fs-4"></i>
            <div>
                <div class="fw-semibold">This wallet is locked</div>
                <div class="small">
                    Locked by {{ $wallet->lockedBy?->name ?? 'a parent' }}{{ $wallet->locked_at ? ' on '.$wallet->locked_at->format('j M Y, g:i A') : '' }}.
                    @if ($wallet->lock_reason)
                        Reason: “{{ $wallet->lock_reason }}”.
                    @endif
                    Spending is paused; you can still add money.
                </div>
            </div>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-lg-5">
            <div class="kw-card balance-card h-100 p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="small opacity-75">Current balance</div>
                    <span class="badge rounded-pill bg-white text-dark px-3 py-2">
                        <i class="bi {{ $wallet->is_locked ? 'bi-lock-fill text-danger' : 'bi-shield-check text-success' }}"></i>
                        {{ $wallet->is_locked ? 'Locked' : 'Active' }}
                    </span>
                </div>
                <div class="balance-amount mt-2">৳{{ number_format($wallet->balance, 2) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-
3 col-lg-35">
        </div>
    </div>
@endsection