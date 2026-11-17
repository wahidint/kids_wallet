@extends('layouts.app')

@section('title', $schedule ? 'Edit allowance' : 'New allowance')

@section('content')
    @php
        $firstName = \Illuminate\Support\Str::before($child->name, ' ');
        $frequency = old('frequency', $schedule->frequency ?? 'weekly');
        $weekdays = \App\Models\AllowanceSchedule::WEEKDAYS;
    @endphp

    <a href="{{ route('parent.children.allowances.index', $child) }}" class="text-secondary text-decoration-none small">
        <i class="bi bi-arrow-left"></i> Back to {{ $firstName }}'s allowances
    </a>

    <div class="row justify-content-center mt-3">
        <div class="col-lg-8 col-xl-6">
            <div class="kw-card p-4">
                <h2 class="h4 fw-bold mb-1">{{ $schedule ? 'Edit allowance' : 'Schedule an allowance' }}</h2>
                <p class="text-secondary mb-4">{{ $firstName }} will receive this automatically on the chosen day.</p>

                <form method="POST" novalidate
                      action="{{ $schedule ? route('parent.children.allowances.update', [$child, $schedule]) : route('parent.children.allowances.store', $child) }}">
                    @csrf
                    @if ($schedule)
                        @method('PUT')
                    @endif

                    <label for="amount" class="form-label fw-medium">Amount</label>
                    <div class="input-group input-group-lg amount-group has-validation mb-3">
                        <span class="input-group-text">৳</span>
                        <input id="amount" name="amount" type="number" step="0.01" min="1" inputmode="decimal" placeholder="0"
                               value="{{ old('amount', $schedule?->amount) }}"
                               @class(['form-control fw-semibold', 'is-invalid' => $errors->has('amount')]) required autofocus>
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-label fw-medium">How often?</div>
                    <div class="d-flex gap-2 mb-3">
                        <input type="radio" class="btn-check" name="frequency" id="freq-weekly" value="weekly" @checked($frequency === 'weekly')>
                        <label class="choice-pill" for="freq-weekly"><i class="bi bi-calendar-week"></i> Weekly</label>
                        <input type="radio" class="btn-check" name="frequency" id="freq-monthly" value="monthly" @checked($frequency === 'monthly')>
                        <label class="choice-pill" for="freq-monthly"><i class="bi bi-calendar-month"></i> Monthly</label>
                    </div>

                    <div id="weeklyField" @class(['mb-3', 'd-none' => $frequency !== 'weekly'])>
                        <label for="day_of_week" class="form-label fw-medium">Pay every</label>
                        <select id="day_of_week" name="day_of_week" @class(['form-select form-select-lg', 'is-invalid' => $errors->has('day_of_week')])>
                            @foreach ($weekdays as $value => $label)
                                <option value="{{ $value }}" @selected((string) old('day_of_week', $schedule?->day_of_week ?? 5) === (string) $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('day_of_week')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div id="monthlyField" @class(['mb-3', 'd-none' => $frequency !== 'monthly'])>
                        <label for="day_of_month" class="form-label fw-medium">Pay on day</label>
                        <select id="day_of_month" name="day_of_month" @class(['form-select form-select-lg', 'is-invalid' => $errors->has('day_of_month')])>
                            @for ($day = 1; $day <= 28; $day++)
                                <option value="{{ $day }}" @selected((int) old('day_of_month', $schedule?->day_of_month ?? 1) === $day)>
                                    {{ \Illuminate\Support\Number::ordinal($day) }} of the month
                                </option>
                            @endfor
                        </select>
                        @error('day_of_month')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Days 1–28 work in every month, including February.</div>
                    </div>

                    <x-form-input name="note" label="Note (optional)" maxlength="60" :value="$schedule?->note"
                                  placeholder="e.g. School tiffin money" help="Shown in the transaction history." />

                    <div class="limit-summary mb-3" id="firstPayment"></div>

                    <button type="submit" class="btn btn-kw btn-lg w-100">
                        <i class="bi bi-calendar-check"></i> {{ $schedule ? 'Save changes' : 'Create schedule' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const weeklyField = document.getElementById('weeklyField');
        const monthlyField = document.getElementById('monthlyField');
        const dayOfWeek = document.getElementById('day_of_week');
        const dayOfMonth = document.getElementById('day_of_month');
        const preview = document.getElementById('firstPayment');

        const isWeekly = () => document.getElementById('freq-weekly').checked;

        // Same rule as the server: first due date on or after today
        const firstDate = () => {
            const date = new Date();
            date.setHours(0, 0, 0, 0);

            if (isWeekly()) {
                while (date.getDay() !== Number(dayOfWeek.value)) date.setDate(date.getDate() + 1);
            } else {
                const day = Number(dayOfMonth.value);
                if (date.getDate() > day) date.setMonth(date.getMonth() + 1);
                date.setDate(day);
            }
            return date;
        };

        const refresh = () => {
            weeklyField.classList.toggle('d-none', !isWeekly());
            monthlyField.classList.toggle('d-none', isWeekly());
            const date = firstDate();
            preview.innerHTML = '<i class="bi bi-calendar-check"></i> First payment: <strong>' +
                date.toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' }) + '</strong>';
        };

        document.querySelectorAll('input[name="frequency"], #day_of_week, #day_of_month')
            .forEach((el) => el.addEventListener('change', refresh));
        refresh();
    </script>
@endpush