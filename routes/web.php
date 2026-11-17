<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Child\DashboardController as ChildDashboardController;
use App\Http\Controllers\Child\InsightsController;
use App\Http\Controllers\Child\PurchaseRequestController;
use App\Http\Controllers\Child\QuizController;
use App\Http\Controllers\Child\RewardController;
use App\Http\Controllers\Child\SavingsGoalController;
use App\Http\Controllers\Child\SpendController;
use App\Http\Controllers\Child\TransactionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Parent\AllowanceController;
use App\Http\Controllers\Parent\ChildController;
use App\Http\Controllers\Parent\ChildInsightsController;
use App\Http\Controllers\Parent\ChildTransactionController;
use App\Http\Controllers\Parent\DashboardController as ParentDashboardController;
use App\Http\Controllers\Parent\PocketMoneyController;
use App\Http\Controllers\Parent\PurchaseApprovalController;
use App\Http\Controllers\Parent\SpendingLimitController;
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

        // F4: Purchase Approval ("can:decide" = only their own children's requests)
        Route::get('/purchases', [PurchaseApprovalController::class, 'index'])->name('purchases.index');
        Route::post('/purchases/{purchase}/approve', [PurchaseApprovalController::class, 'approve'])
            ->name('purchases.approve')->can('decide', 'purchase');
        Route::post('/purchases/{purchase}/reject', [PurchaseApprovalController::class, 'reject'])
            ->name('purchases.reject')->can('decide', 'purchase');

        // Everything about ONE child. "can:manage-child,child" = only their own children.
        Route::middleware('can:manage-child,child')
            ->prefix('children/{child}')
            ->name('children.')
            ->group(function () {
                Route::get('/', [ChildController::class, 'show'])->name('show');

                // F1: Digital Pocket Money
                Route::get('/add-money', [PocketMoneyController::class, 'create'])->name('pocket-money.create');
                Route::post('/add-money', [PocketMoneyController::class, 'store'])->name('pocket-money.store');

                // F2: Smart Spending Limits
                Route::get('/limits', [SpendingLimitController::class, 'edit'])->name('limits.edit');
                Route::put('/limits', [SpendingLimitController::class, 'update'])->name('limits.update');

                // F5: Smart Expense Tracker
                Route::get('/transactions', [ChildTransactionController::class, 'index'])->name('transactions.index');
                Route::get('/transactions/export', [ChildTransactionController::class, 'export'])->name('transactions.export');

                // F7: Allowance Scheduler
                Route::get('/allowances', [AllowanceController::class, 'index'])->name('allowances.index');
                Route::get('/allowances/new', [AllowanceController::class, 'create'])->name('allowances.create');
                Route::post('/allowances', [AllowanceController::class, 'store'])->name('allowances.store');
                Route::get('/allowances/{schedule}/edit', [AllowanceController::class, 'edit'])->name('allowances.edit');
                Route::put('/allowances/{schedule}', [AllowanceController::class, 'update'])->name('allowances.update');
                Route::patch('/allowances/{schedule}/toggle', [AllowanceController::class, 'toggle'])->name('allowances.toggle');
                Route::delete('/allowances/{schedule}', [AllowanceController::class, 'destroy'])->name('allowances.destroy');

                // F8: Spending Insights
                Route::get('/insights', [ChildInsightsController::class, 'index'])->name('insights');

                // F9: Emergency Wallet Lock
                Route::post('/lock', [WalletLockController::class, 'store'])->name('lock');
                Route::delete('/lock', [WalletLockController::class, 'destroy'])->name('unlock');
            });
    });

    // ---------------- Child-only pages ----------------
    // "badges" (F10) checks for newly earned badges on every child page
    Route::middleware(['role:child', 'badges'])->prefix('child')->name('child.')->group(function () {
        Route::get('/dashboard', [ChildDashboardController::class, 'index'])->name('dashboard');

        // F2: Child spending (limits are checked inside WalletService)
        Route::get('/spend', [SpendController::class, 'create'])->name('spend.create');
        Route::post('/spend', [SpendController::class, 'store'])->name('spend.store');

        // F3: Savings Goals ("can:manage" = only their own goals)
        Route::get('/goals', [SavingsGoalController::class, 'index'])->name('goals.index');
        Route::get('/goals/new', [SavingsGoalController::class, 'create'])->name('goals.create');
        Route::post('/goals', [SavingsGoalController::class, 'store'])->name('goals.store');
        Route::post('/goals/{goal}/contribute', [SavingsGoalController::class, 'contribute'])
            ->name('goals.contribute')->can('manage', 'goal');
        Route::post('/goals/{goal}/withdraw', [SavingsGoalController::class, 'withdraw'])
            ->name('goals.withdraw')->can('manage', 'goal');
        Route::delete('/goals/{goal}', [SavingsGoalController::class, 'destroy'])
            ->name('goals.destroy')->can('manage', 'goal');

        // F4: Purchase requests ("can:manageOwn" = only their own requests)
        Route::get('/purchases', [PurchaseRequestController::class, 'index'])->name('purchases.index');
        Route::get('/purchases/new', [PurchaseRequestController::class, 'create'])->name('purchases.create');
        Route::post('/purchases', [PurchaseRequestController::class, 'store'])->name('purchases.store');
        Route::post('/purchases/{purchase}/complete', [PurchaseRequestController::class, 'complete'])
            ->name('purchases.complete')->can('manageOwn', 'purchase');
        Route::post('/purchases/{purchase}/cancel', [PurchaseRequestController::class, 'cancel'])
            ->name('purchases.cancel')->can('manageOwn', 'purchase');

        // F5: Smart Expense Tracker
        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transactions/export', [TransactionController::class, 'export'])->name('transactions.export');

        // F6: Financial Learning Games
        Route::get('/quizzes', [QuizController::class, 'index'])->name('quizzes.index');
        Route::get('/quizzes/attempts/{attempt}', [QuizController::class, 'result'])->name('quizzes.result');
        Route::get('/quizzes/{quiz:slug}', [QuizController::class, 'play'])->name('quizzes.play');
        Route::post('/quizzes/{quiz:slug}/check', [QuizController::class, 'check'])
            ->name('quizzes.check')->middleware('throttle:60,1');
        Route::post('/quizzes/{quiz:slug}/finish', [QuizController::class, 'finish'])->name('quizzes.finish');

        // F8: Spending Insights
        Route::get('/insights', [InsightsController::class, 'index'])->name('insights');

        // F10: Savings Rewards
        Route::get('/rewards', [RewardController::class, 'index'])->name('rewards');
    });
});