<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Demo limits so Feature 2 has something to show.
 * Format: [daily, weekly, monthly]. Tasnim has none.
 */
class DemoSpendingLimitSeeder extends Seeder
{
    public function run(): void
    {
        $rahim = User::where('email', 'rahim@demo.com')->first();

        $limits = [
            'ayaan@demo.com' => [200, 800, 2500],
            'maya@demo.com' => [100, 400, 1200],
        ];

        foreach ($limits as $email => [$daily, $weekly, $monthly]) {
            $wallet = User::where('email', $email)->firstOrFail()->wallet;

            $wallet->spendingLimit()->updateOrCreate([], [
                'daily_limit' => $daily,
                'weekly_limit' => $weekly,
                'monthly_limit' => $monthly,
                'updated_by' => $rahim?->id,
            ]);
        }
    }
}