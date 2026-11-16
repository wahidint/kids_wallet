@extends('layouts.guest')

@section('title', 'Forgot password')

@section('content')
    <h1 class="h3 fw-bold mb-1">Forgot your password?</h1>
    <p class="text-secondary mb-4">Enter your email and we'll send you a link to choose a new one.</p>

    <form method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf
        <x-form-input name="email" label="Email address" type="email" required autofocus autocomplete="username" />
        <button type="submit" class="btn btn-kw btn-lg w-100">Send reset link</button>
    </form>

    <p class="text-center mt-4 mb-0">
        <a href="{{ route('login') }}" class="text-kw fw-medium text-decoration-none">
            <i class="bi bi-arrow-left"></i> Back to log in
        </a>
    </p>
@endsection