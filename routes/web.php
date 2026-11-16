<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Child\DashboardController as ChildDashboardController;
use App\Http\Controllers\Child\SpendController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Parent\ChildController;
use App\Http\Controllers\Parent\DashboardController as ParentDashboardController;
use App\Http\Controllers\Parent\PocketMoneyController;
use App\Http\Controllers\Parent\WalletLockController;
use Illuminate\Support\Facades\Route;

// Public landing page
Route::view('/', 'welcome')->name('home');

// Only for people who are NOT logged in
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);

    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:10,1');

    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])->name('password.store');
});

// Only for logged-in users
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Sends each user to their own dashboard
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // ---------------- Parent-only pages ----------------
    Route::middleware('role:parent')->prefix('parent')->name('parent.')->group(function () {
        Route::get('/dashboard', [ParentDashboardController::class, 'index'])->name('dashboard');

        // Everything about ONE child. "can:manage-child,child" = only their own children.
        Route::middleware('can:manage-child,child')
            ->prefix('children/{child}')
            ->name('children.')
            ->group(function () {
                Route::get('/', [ChildController::class, 'show'])->name('show');

                // F1: Digital Pocket Money
                Route::get('/add-money', [PocketMoneyController::class, 'create'])->name('pocket-money.create');
                Route::post('/add-money', [PocketMoneyController::class, 'store'])->name('pocket-money.store');

                // F9: Emergency Wallet Lock
                Route::post('/lock', [WalletLockController::class, 'store'])->name('lock');
                Route::delete('/lock', [WalletLockController::class, 'destroy'])->name('unlock');
            });
    });

    // ---------------- Child-only pages ----------------
    Route::middleware('role:child')->prefix('child')->name('child.')->group(function () {
        Route::get('/dashboard', [ChildDashboardController::class, 'index'])->name('dashboard');

        // F2: Child spending (limits are checked inside WalletService)
        Route::get('/spend', [SpendController::class, 'create'])->name('spend.create');
        Route::post('/spend', [SpendController::class, 'store'])->name('spend.store');
    });
});