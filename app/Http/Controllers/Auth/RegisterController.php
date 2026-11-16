<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\RegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request, RegistrationService $registration): RedirectResponse
    {
        $user = $registration->register($request->validated());

        // Log the new user in straight away
        Auth::login($user);
        $request->session()->regenerate();

        $message = $user->isChild()
            ? 'Welcome! Your wallet is ready and linked to your parent.'
            : 'Welcome! Share your family link code with your child so they can join.';

        return redirect()->route('dashboard')->with('success', $message);
    }
}