@extends('layouts.app')

@section('title', 'Spending limits')

@section('content')
    @php
        $firstName = \Illuminate\Support\Str::before($child->name, ' ');
        $usage = $wallet->limitUsage();
        $fields = [
            'daily' => ['Daily limit', 'Resets every night at midnight.'],
            'weekly' => ['Weekly limit', 'Resets every Saturday.'],
            'monthly' => ['Monthly limit', 'Resets on the 1st of each month.'],
        ];
    @endphp

    <a href="{{ route('parent.children.show', $child) }}" class="text-secondary text-decoration-none small">
        <i class="bi bi-arrow-left"></i> Back to {{ $firstName }}'s wallet
    </a>

    <div class="row g-4 mt-1">
        <div class="col-lg-7">
            <div class="kw-card p-4">
                <h2 class="h4 fw-bold mb-1">Spending limits for {{ $firstName }}</h2>
                <p class="text-secondary mb-4">
                    Leave a box empty for no limit. Saving money into goals never counts as spending.
                </p>

                <form method="POST" action="{{ route('parent.children.limits.update', $child) }}" novalidate>
                    @csrf
                    @method('PUT')

                    @foreach ($fields as $period => [$label, $help])
                        @php $name = $period.'_limit'; @endphp
                        <div class="mb-3">
                            <label for="{{ $name }}" class="form-label fw-medium">{{ $label }}</label>
                            <div class="input-group input-group-lg amount-group has-validation">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" min="1" id="{{ $name }}" name="{{ $name }}"
                                       inputmode="decimal" placeholder="No limit"
                                       value="{{ old($name, $limit?->{$name}) }}"
                                       @class(['form-control', 'is-invalid' => $errors->has($name)])>
                                @error($name)
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text">{{ $help }}</div>
                        </div>
                    @endforeach

                    <button type="submit" class="btn btn-kw btn-lg w-100 mt-2">
                        <i class="bi bi-check2-circle"></i> Save limits
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="kw-card">
                <h3 class="h6 fw-semibold mb-3">{{ $firstName }}'s spending right now</h3>
                @forelse ($usage as $row)
                    @include('partials.limit-usage', ['usage' => [$row]])
                @empty
                    <p class="text-secondary small mb-0">No limits set yet. Once you save some, progress bars will appear here.</p>
                @endforelse
            </div>

            <div class="tip-card mt-3">
                <i class="bi bi-lightbulb"></i>
                <div>
                    <span class="fw-semibold">Tip:</span> Start a little above what {{ $firstName }} usually spends,
                    then tighten it as good habits grow.
                </div>
            </div>
        </div>
    </div>
@endsection