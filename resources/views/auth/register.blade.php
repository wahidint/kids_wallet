@extends('layouts.guest')

@section('title', 'Create account')

@section('content')
    @php $role = old('role', request('role') === 'child' ? 'child' : 'parent'); @endphp

    <h1 class="h3 fw-bold mb-1">Create your account</h1>
    <p class="text-secondary mb-4">
        Already have one? <a href="{{ route('login') }}" class="text-kw fw-medium">Log in</a>
    </p>

    <form method="POST" action="{{ route('register') }}" novalidate>
        @csrf

        {{-- Role picker --}}
        <div class="row g-2 mb-4">
            @foreach ([
                'parent' => ['bi-person-badge', "I'm a parent", "Manage my children's wallets"],
                'child'  => ['bi-emoji-smile', "I'm a child", "Join with my parent's code"],
            ] as $value => [$icon, $title, $hint])
                <div class="col-6">
                    <input type="radio" class="btn-check" name="role" id="role-{{ $value }}"
                           value="{{ $value }}" @checked($role === $value) autocomplete="off">
                    <label class="role-option w-100 h-100" for="role-{{ $value }}">
                        <i class="bi {{ $icon }}"></i>
                        <span class="fw-semibold">{{ $title }}</span>
                        <span class="small text-secondary">{{ $hint }}</span>
                    </label>
                </div>
            @endforeach
            @error('role')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <x-form-input name="name" label="Full name" required autocomplete="name" />
        <x-form-input name="email" label="Email address" type="email" required autocomplete="username" />

        {{-- Child-only fields --}}
        <div id="child-fields" @class(['d-none' => $role !== 'child'])>
            <x-form-input name="date_of_birth" label="Date of birth" type="date"
                          max="{{ now()->toDateString() }}" help="{{ config('app.name') }} is for children under 16." />
            <x-form-input name="link_code" label="Family link code" class="link-code-input" maxlength="6"
                          autocomplete="off" help="Ask your parent: it's shown on their dashboard." />
        </div>

        {{-- Parent-only fields --}}
        <div id="parent-fields" @class(['d-none' => $role === 'child'])>
            <x-form-input name="phone" label="Mobile number (optional)" type="tel" placeholder="01712345678"
                          autocomplete="tel" />
        </div>

        <x-form-input name="password" label="Password" type="password" required autocomplete="new-password"
                      help="At least 8 characters, with letters and numbers." />
        <x-form-input name="password_confirmation" label="Confirm password" type="password" required
                      autocomplete="new-password" />

        <button type="submit" class="btn btn-kw btn-lg w-100 mt-2">Create account</button>
    </form>
@endsection

@push('scripts')
    <script>
        const childFields = document.getElementById('child-fields');
        const parentFields = document.getElementById('parent-fields');

        document.querySelectorAll('input[name="role"]').forEach((radio) => {
            radio.addEventListener('change', () => {
                const isChild = document.getElementById('role-child').checked;
                childFields.classList.toggle('d-none', !isChild);
                parentFields.classList.toggle('d-none', isChild);
            });
        });

        // Show the link code in capitals as the child types it
        document.getElementById('link_code').addEventListener('input', (event) => {
            event.target.value = event.target.value.toUpperCase();
        });
    </script>
@endpush