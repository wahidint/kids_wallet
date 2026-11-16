@extends('layouts.app')

@section('title', 'New goal')

@section('content')
    <a href="{{ route('child.goals.index') }}" class="text-secondary text-decoration-none small">
        <i class="bi bi-arrow-left"></i> Back to my goals
    </a>

    <div class="row justify-content-center mt-3">
        <div class="col-lg-8 col-xl-6">
            <div class="kw-card p-4">
                <h2 class="h4 fw-bold mb-1">Create a savings goal</h2>
                <p class="text-secondary mb-4">Pick something you'd love, set a target and a date, then start saving!</p>

                <form method="POST" action="{{ route('child.goals.store') }}" novalidate>
                    @csrf

                    <x-form-input name="title" label="What are you saving for?" maxlength="80"
                                  placeholder="e.g. New bicycle" required autofocus />

                    <div class="form-label fw-medium">Pick an icon</div>
                    <div class="d-flex flex-wrap gap-2 mb-1">
                        @foreach ($icons as $icon => $label)
                            <input type="radio" class="btn-check" name="icon" id="icon-{{ $loop->index }}"
                                   value="{{ $icon }}" @checked(old('icon', 'bi-piggy-bank') === $icon)>
                            <label class="choice-pill" for="icon-{{ $loop->index }}">
                                <i class="bi {{ $icon }}"></i> {{ $label }}
                            </label>
                        @endforeach
                    </div>
                    @error('icon')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror

                    <label for="target_amount" class="form-label fw-medium mt-3">Target amount</label>
                    <div class="input-group input-group-lg amount-group has-validation mb-3">
                        <span class="input-group-text">৳</span>
                        <input id="target_amount" name="target_amount" type="number" step="0.01" min="10"
                               inputmode="decimal" placeholder="0" value="{{ old('target_amount') }}"
                               @class(['form-control fw-semibold', 'is-invalid' => $errors->has('target_amount')]) required>
                        @error('target_amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <x-form-input name="deadline" label="When do you want it by?" type="date"
                                  min="{{ now()->addDay()->toDateString() }}" required />

                    <div class="limit-summary mb-3 d-none" id="planPreview">
                        <i class="bi bi-calendar-check"></i>
                        <span id="planText"></span>
                    </div>

                    <button type="submit" class="btn btn-kw btn-lg w-100">
                        <i class="bi bi-flag"></i> Create goal
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const targetInput = document.getElementById('target_amount');
        const deadlineInput = document.getElementById('deadline');
        const preview = document.getElementById('planPreview');
        const planText = document.getElementById('planText');

        // Live plan: "Save about ৳150 a week for 8 weeks"
        const updatePlan = () => {
            const target = parseFloat(targetInput.value);
            const deadline = new Date(deadlineInput.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const days = Math.round((deadline - today) / 86400000);

            if (!(target > 0) || isNaN(days) || days <= 0) {
                preview.classList.add('d-none');
                return;
            }

            const weeks = Math.max(1, Math.ceil(days / 7));
            const perWeek = Math.ceil(target / weeks);
            planText.innerHTML = `Plan: save about <strong>৳${perWeek.toLocaleString('en-US')}</strong> a week for ${weeks} ${weeks === 1 ? 'week' : 'weeks'}.`;
            preview.classList.remove('d-none');
        };

        targetInput.addEventListener('input', updatePlan);
        deadlineInput.addEventListener('input', updatePlan);
        updatePlan();
    </script>
@endpush