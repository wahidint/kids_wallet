<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\AllowanceService;
use Illuminate\Database\Seeder;

/**
 * Demo schedules that match the seeded history (allowances paid every Friday).
 */
class DemoAllowanceSeeder extends Seeder
{
    public function run(AllowanceService $allowances): void
    {
        $rahim = User::where('email', 'rahim@demo.com')->firstOrFail();
        $nasreen = User::where('email', 'nasreen@demo.com')->firstOrFail();
        $karim = User::where('email', 'karim@demo.com')->firstOrFail();

        $allowances->create(User::where('email', 'ayaan@demo.com')->firstOrFail(), $rahim, [
            'amount' => 300, 'frequency' => 'weekly', 'day_of_week' => 5, 'note' => 'Weekly allowance',
        ]);

        $allowances->create(User::where('email', 'maya@demo.com')->firstOrFail(), $nasreen, [
            'amount' => 150, 'frequency' => 'weekly', 'day_of_week' => 5, 'note' => 'Weekly allowance',
        ]);

        $allowances->create(User::where('email', 'tasnim@demo.com')->firstOrFail(), $karim, [
            'amount' => 1000, 'frequency' => 'monthly', 'day_of_month' => 1, 'note' => 'Monthly pocket money',
        ]);
    }
}