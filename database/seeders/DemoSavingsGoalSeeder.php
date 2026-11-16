<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\SavingsService;
use Illuminate\Database\Seeder;

/**
 * Demo goals in different states, created through the real service.
 */
class DemoSavingsGoalSeeder extends Seeder
{
    public function run(SavingsService $savings): void
    {
        $ayaan = User::where('email', 'ayaan@demo.com')->firstOrFail();
        $maya = User::where('email', 'maya@demo.com')->firstOrFail();
        $tasnim = User::where('email', 'tasnim@demo.com')->firstOrFail();

        // Achieved goal (shows the trophy badge)
        $books = $savings->create($ayaan, [
            'title' => 'Story books',
            'icon' => 'bi-book',
            'target_amount' => 150,
            'deadline' => now()->addDays(10)->toDateString(),
        ]);
        $savings->contribute($books, $ayaan, 150);

        // In progress
        $boots = $savings->create($ayaan, [
            'title' => 'Football boots',
            'icon' => 'bi-trophy',
            'target_amount' => 1800,
            'deadline' => now()->addDays(45)->toDateString(),
        ]);
        $savings->contribute($boots, $ayaan, 120);

        // Just started (nothing saved yet, so it can be deleted)
        $savings->create($ayaan, [
            'title' => 'New bicycle',
            'icon' => 'bi-bicycle',
            'target_amount' => 5000,
            'deadline' => now()->addDays(120)->toDateString(),
        ]);

        $art = $savings->create($maya, [
            'title' => 'Art set',
            'icon' => 'bi-palette',
            'target_amount' => 400,
            'deadline' => now()->addDays(30)->toDateString(),
        ]);
        $savings->contribute($art, $maya, 50);

        // Tasnim's wallet is locked, so she can't save into it yet
        $savings->create($tasnim, [
            'title' => 'Science kit',
            'icon' => 'bi-gift',
            'target_amount' => 1500,
            'deadline' => now()->addDays(60)->toDateString(),
        ]);
    }
}