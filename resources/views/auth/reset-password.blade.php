@extends('layouts.guest')

@section('title', 'Reset password')

@section('content')
    <h1 class="h3 fw-bold mb-1">Choose a new password</h1>
    <p class="text-secondary mb-4">Make it something you haven't used here before.</p>

    <form method="POST" action="{{ route('password.store') }}" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <x-form-input name="email" label="Email address" type="email" :value="$email" required autocomplete="username" />
        <x-form-input name="password" label="New password" type="password" required autofocus autocomplete="new-password"
                      help="At least 8 characters, with letters and numbers." />
        <x-form-input name="password_confirmation" label="Confirm new password" type="password" required
                      autocomplete="new-password" />

        <button type="submit" class="btn btn-kw btn-lg w-100">Reset password</button>
    </form>
@endsection