@extends('layouts.app')

@section('title', 'Ask to buy')

@section('content')
    <a href="{{ route('child.purchases.index') }}" class="text-secondary text-decoration-none small">
        <i class="bi bi-arrow-left"></i> Back to my requests
    </a>

    <div class="row justify-content-center mt-3">
        <div class="col-lg-8 col-xl-6">
            <div class="kw-card p-4">
                <h2 class="h4 fw-bold mb-1">Ask to buy something</h2>
                <p class="text-secondary mb-4">
                    Your parents will see your request. Money is only taken after it's approved <em>and</em> you buy it.
                    Your balance: <strong>৳{{ number_format($wallet->balance, 2) }}</strong>
                </p>

                <form method="POST" action="{{ route('child.purchases.store') }}" id="requestForm" novalidate>
                    @csrf

                    <x-form-input name="item_name" label="What do you want to buy?" maxlength="100"
                                  placeholder="e.g. Cricket bat" required autofocus />

                    <label for="amount" class="form-label fw-medium">How much does it cost?</label>
                    <div class="input-group input-group-lg amount-group has-validation mb-3">
                        <span class="input-group-text">৳</span>
                        <input id="amount" name="amount" type="number" step="0.01" min="1"
                               inputmode="decimal" placeholder="0" value="{{ old('amount') }}"
                               @class(['form-control fw-semibold', 'is-invalid' => $errors->has('amount')]) required>
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-label fw-medium">Category</div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($categories as $category)
                            <input type="radio" class="btn-check" name="category_id" id="category-{{ $category->id }}"
                                   value="{{ $category->id }}" @checked((int) old('category_id') === $category->id)>
                            <label class="choice-pill" for="category-{{ $category->id }}">
                                <i class="bi {{ $category->icon }}" style="color: {{ $category->color }};"></i> {{ $category->name }}
                            </label>
                        @endforeach
                    </div>
                    @error('category_id')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror

                    <div class="mt-4 mb-3">
                        <label for="description" class="form-label fw-medium">Why do you want it?</label>
                        <textarea id="description" name="description" rows="3" maxlength="300"
                                  placeholder="e.g. For the inter-school cricket tournament next month."
                                  @class(['form-control', 'is-invalid' => $errors->has('description')])>{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-kw btn-lg w-100" id="submitButton">
                        <i class="bi bi-send"></i> Send request to my parents
                    </button>
                </form>
            </div>

            <div class="tip-card mt-3">
                <i class="bi bi-lightbulb"></i>
                <div><span class="fw-semibold">Tip:</span> A good reason helps! Parents are more likely to say yes when you explain why you need it.</div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('requestForm').addEventListener('submit', () => {
            const button = document.getElementById('submitButton');
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Sending...';
        });
    </script>
@endpush