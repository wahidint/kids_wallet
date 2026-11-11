<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Child\DashboardController as ChildDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Parent\DashboardController as ParentDashboardController;
use Illuminate\Support\Facades\Route;

// Public landing page
Route::view('/', 'welcome')->name('home');

// Only for people who are NOT logged in
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

// Only for logged-in users
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Sends each user to their own dashboard
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Parent-only pages: URLs start with /parent, names start with "parent."
    Route::middleware('role:parent')->prefix('parent')->name('parent.')->group(function () {
        Route::get('/dashboard', [ParentDashboardController::class, 'index'])->name('dashboard');
    });

    // Child-only pages: URLs start with /child, names start with "child."
    Route::middleware('role:child')->prefix('child')->name('child.')->group(function () {
        Route::get('/dashboard', [ChildDashboardController::class, 'index'])->name('dashboard');
    });
});