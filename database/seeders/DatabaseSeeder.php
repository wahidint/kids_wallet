<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Runs when you type: php artisan db:seed (or migrate:fresh --seed).
     * Order matters: categories first, then history, then features that use the balances.
     */
    public function run(): void
    {
        $this->call([
            SpendingCategorySeeder::class,
            DemoDataSeeder::class,
            DemoSpendingLimitSeeder::class,
            DemoPurchaseRequestSeeder::class,
            DemoSavingsGoalSeeder::class,
            DemoAllowanceSeeder::class,
        ]);
    }
}