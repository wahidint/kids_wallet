@extends('layouts.app')

@section('title', 'Add money')

@section('content')
    @php $firstName = \Illuminate\Support\Str::before($child->name, ' '); @endphp

    <a href="{{ route('parent.children.show', $child) }}" class="text-secondary text-decoration-none small">
        <i class="bi bi-arrow-left"></i> Back to {{ $firstName }}'s wallet
    </a>

    <div class="row justify-content-center mt-3">
        <div class="col-lg-8 col-xl-6">
            <div class="kw-card p-4">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="avatar avatar-lg">{{ mb_strtoupper(mb_substr($child->name, 0, 1)) }}</div>
                    <div>
                        <div class="fw-semibold">Sending to {{ $child->name }}</div>
                        <div class="small text-secondary">Current balance: ৳{{ number_format($wallet->balance, 2) }}</div>
                    </div>
                </div>

                <form method="POST" action="{{ route('parent.children.pocket-money.store', $child) }}" id="addMoneyForm" novalidate>
                    @csrf

                    <label for="amount" class="form-label fw-medium">Amount</label>
                    <div class="input-group input-group-lg amount-group has-validation mb-2">
                        <span class="input-group-text">৳</span>
                        <input id="amount" name="amount" type="number" step="0.01" min="1" max="10000"
                               inputmode="decimal" placeholder="0" value="{{ old('amount') }}"
                               @class(['form-control fw-semibold', 'is-invalid' => $errors->has('amount')])
                               required autofocus>
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex flex-wrap gap-2 mb-4">
                        @foreach ([50, 100, 200, 500, 1000] as $quick)
                            <button type="button" class="btn btn-light border rounded-pill quick-amount" data-amount="{{ $quick }}">
                                ৳{{ number_format($quick) }}
                            </button>
                        @endforeach
                    </div>

                    <div class="form-label fw-medium">What's it for?</div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($occasions as $occasion)
                            <input type="radio" class="btn-check" name="occasion" id="occasion-{{ $occasion->value }}"
                                   value="{{ $occasion->value }}" @checked(old('occasion', 'pocket_money') === $occasion->value)>
                            <label class="choice-pill" for="occasion-{{ $occasion->value }}">
                                <i class="bi {{ $occasion->icon() }}"></i> {{ $occasion->label() }}
                            </label>
                        @endforeach
                    </div>
                    @error('occasion')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror

                    <div class="mt-4">
                        <x-form-input name="note" :label="'Note for '.$firstName.' (optional)'"
                                      placeholder="e.g. Well done on your maths test!" maxlength="100" />
                    </div>

                    <button type="submit" class="btn btn-kw btn-lg w-100 mt-2" id="submitButton">
                        <i class="bi bi-send"></i> Add <span id="submitAmount">money</span>
                    </button>

                    <p class="text-center small text-secondary mt-3 mb-0">
                        <i class="bi bi-shield-check"></i> Simulated money only: no real transfer takes place.
                    </p>
                </form>
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
            submitAmount.textContent = value > 0 ? formatTaka(value) : 'money';
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

        // Stop double-clicks from submitting the form twice
        document.getElementById('addMoneyForm').addEventListener('submit', () => {
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Adding...';
        });
    </script>
@endpush