@extends('layouts.app')

@section('title', 'Spend money')

@section('content')
    @php
        $usage = $wallet->limitUsage();
        $tightest = collect($usage)->sortBy(fn ($row) => (float) $row['remaining'])->first();
        $tip = \Illuminate\Support\Arr::random([
            'Before you buy, ask: is it a need or a want?',
            'Waiting one day before buying something big helps you decide if you really want it.',
            'Small spends add up. ৳20 every day is ৳600 a month!',
            'Compare prices before you buy. The same thing can cost less somewhere else.',
            'Saving a little from every allowance makes big goals possible.',
        ]);
    @endphp

    <a href="{{ route('child.dashboard') }}" class="text-secondary text-decoration-none small">
        <i class="bi bi-arrow-left"></i> Back to my wallet
    </a>

    <div class="row justify-content-center mt-3">
        <div class="col-lg-8 col-xl-6">

            @if ($wallet->is_locked)
                <div class="alert alert-warning d-flex gap-3 align-items-start rounded-4">
                    <i class="bi bi-lock-fill fs-4"></i>
                    <div>
                        <div class="fw-semibold">Your wallet is locked</div>
                        <div class="small">
                            {{ $wallet->lockedBy?->name ?? 'A parent' }} paused spending.
                            @if ($wallet->lock_reason)
                                Reason: “{{ $wallet->lock_reason }}”.
                            @endif
                            Ask them to unlock it.
                        </div>
                    </div>
                </div>
            @endif

            <div class="kw-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="small text-secondary">Available balance</div>
                        <div class="fs-3 fw-bold">৳{{ number_format($wallet->balance, 2) }}</div>
                    </div>
                    <div class="stat-icon"><i class="bi bi-bag-check"></i></div>
                </div>

                @if ($tightest)
                    <div @class([
                            'limit-summary mb-4',
                            'is-warning' => $tightest['status'] === 'warning',
                            'is-reached' => $tightest['status'] === 'reached',
                         ])>
                        <i class="bi bi-speedometer2"></i>
                        @if ($tightest['status'] === 'reached')
                            You've reached your {{ $tightest['period'] }} spending limit.
                        @else
                            You can spend up to <strong>৳{{ number_format($tightest['remaining'], 2) }}</strong>
                            more {{ strtolower($tightest['label']) }}.
                        @endif
                    </div>
                @endif

                <form method="POST" action="{{ route('child.spend.store') }}" id="spendForm" novalidate>
                    @csrf

                    <label for="amount" class="form-label fw-medium">How much?</label>
                    <div class="input-group input-group-lg amount-group has-validation mb-2">
                        <span class="input-group-text">৳</span>
                        <input id="amount" name="amount" type="number" step="0.01" min="1"
                               inputmode="decimal" placeholder="0" value="{{ old('amount') }}"
                               @class(['form-control fw-semibold', 'is-invalid' => $errors->has('amount')])
                               required autofocus @disabled($wallet->is_locked)>
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex flex-wrap gap-2 mb-4">
                        @foreach ([20, 50, 100, 200] as $quick)
                            <button type="button" class="btn btn-light border rounded-pill quick-amount" data-amount="{{ $quick }}" @disabled($wallet->is_locked)>
                                ৳{{ $quick }}
                            </button>
                        @endforeach
                    </div>

                    <div class="form-label fw-medium">What kind of spending?</div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($categories as $category)
                            <input type="radio" class="btn-check" name="category_id" id="category-{{ $category->id }}"
                                   value="{{ $category->id }}" @checked((int) old('category_id') === $category->id)
                                   @disabled($wallet->is_locked)>
                            <label class="choice-pill" for="category-{{ $category->id }}">
                                <i class="bi {{ $category->icon }}" style="color: {{ $category->color }};"></i> {{ $category->name }}
                            </label>
                        @endforeach
                    </div>
                    @error('category_id')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror

                    <div class="mt-4">
                        <x-form-input name="description" label="What did you buy?" maxlength="100"
                                      placeholder="e.g. Fuchka with friends" :disabled="$wallet->is_locked" />
                    </div>

                    <button type="submit" class="btn btn-kw btn-lg w-100 mt-2" id="submitButton" @disabled($wallet->is_locked)>
                        <i class="bi bi-bag-check"></i> Pay <span id="submitAmount"></span>
                    </button>
                </form>
            </div>

            <div class="tip-card mt-3">
                <i class="bi bi-lightbulb"></i>
                <div><span class="fw-semibold">Money tip:</span> {{ $tip }}</div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const amountInput = document.getElementById('amount');
        const submitAmount = document.getElementById('submitAmount');
        const submitButton = document.getElementById('submitButton');
        const quickButtons = document.querySelectorAll('.quick-amount');

        const formatTaka = (value) =>
            '৳' + value.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });

        const refresh = () => {
            const value = parseFloat(amountInput.value);
            submitAmount.textContent = value > 0 ? formatTaka(value) : '';
            quickButtons.forEach((button) =>
                button.classList.toggle('active', Number(button.dataset.amount) === value));
        };

        quickButtons.forEach((button) => button.addEventListener('click', () => {
            amountInput.value = button.dataset.amount;
            refresh();
            amountInput.focus();
        }));

        amountInput.addEventListener('input', refresh);
        refresh();

        document.getElementById('spendForm').addEventListener('submit', () => {
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Paying...';
        });
    </script>
@endpush