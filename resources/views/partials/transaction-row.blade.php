@php
    $isCredit = $transaction->type === \App\Enums\TransactionType::Credit;
    $category = $transaction->category;
    $icon = $category->icon ?? ($isCredit ? 'bi-arrow-down-left' : 'bi-arrow-up-right');
    $color = $category->color ?? ($isCredit ? '#16A34A' : '#DC2626');
@endphp

<div class="tx-row">
    <div class="tx-icon" style="background: {{ $color }}1A; color: {{ $color }};">
        <i class="bi {{ $icon }}"></i>
    </div>
    <div class="flex-grow-1 min-w-0">
        <div class="fw-medium text-truncate">{{ $transaction->description ?? $transaction->source->label() }}</div>
        <div class="small text-secondary text-truncate">
            @if (! empty($showChild))
                {{ $transaction->wallet->child->name }} ·
            @endif
            {{ $category->name ?? $transaction->source->label() }} · {{ $transaction->created_at->format('j M, g:i A') }}
        </div>
    </div>
    <div class="{{ $isCredit ? 'amount-credit' : 'amount-debit' }} text-nowrap">
        {{ $isCredit ? '+' : '−' }}৳{{ number_format($transaction->amount, 2) }}
    </div>
</div>