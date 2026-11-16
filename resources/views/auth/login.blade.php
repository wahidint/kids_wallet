@extends('layouts.guest')

@section('title', 'Log in')

@section('content')
    <h1 class="h3 fw-bold mb-1">Welcome back 👋</h1>
    <p class="text-secondary mb-4">
        New here? <a href="{{ route('register') }}" class="text-kw fw-medium">Create an account</a>
    </p>

    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        <x-form-input name="email" label="Email address" type="email" required autofocus autocomplete="username" />
        <x-form-input name="password" label="Password" type="password" required autocomplete="current-password" />

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check mb-0">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" @checked(old('remember'))>
                <label class="form-check-label" for="remember">Remember me</label>
            </div>
            <a href="{{ route('password.request') }}" class="small text-kw text-decoration-none">Forgot password?</a>
        </div>

        <button type="submit" class="btn btn-kw btn-lg w-100">Log in</button>
    </form>

    @if (app()->environment('local'))
        <div class="demo-box mt-4">
            <div class="small fw-semibold mb-2">
                <i class="bi bi-info-circle text-kw"></i> Demo accounts
                <span class="text-secondary fw-normal">(click to fill · password: password)</span>
            </div>
            <div class="list-group list-group-flush small">
                @foreach ([
                    ['Rahim Ahmed', 'rahim@demo.com', 'Parent'],
                    ['Nasreen Ahmed', 'nasreen@demo.com', 'Parent'],
                    ['Karim Hossain', 'karim@demo.com', 'Parent'],
                    ['Ayaan Ahmed', 'ayaan@demo.com', 'Child'],
                    ['Maya Ahmed', 'maya@demo.com', 'Child'],
                    ['Tasnim Hossain', 'tasnim@demo.com', 'Child'],
                ] as [$name, $email, $role])
                    <button type="button" class="list-group-item list-group-item-action demo-login d-flex justify-content-between align-items-center" data-email="{{ $email }}">
                        <span>{{ $name }} <span class="text-secondary">· {{ $email }}</span></span>
                        <span class="badge rounded-pill text-bg-light">{{ $role }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        @push('scripts')
            <script>
                document.querySelectorAll('.demo-login').forEach((button) => {
                    button.addEventListener('click', () => {
                        document.getElementById('email').value = button.dataset.email;
                        document.getElementById('password').value = 'password';
                        document.getElementById('password').focus();
                    });
                });
            </script>
        @endpush
    @endif
@endsection