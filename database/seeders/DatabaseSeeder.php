<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Runs when you type: php artisan db:seed (or migrate:fresh --seed).
     * App content first, then demo data in dependency order.
     */
    public function run(): void
    {
        $this->call([
            // App content
            SpendingCategorySeeder::class,
            QuizSeeder::class,

            // Demo data
            DemoDataSeeder::class,
            DemoSpendingLimitSeeder::class,
            DemoPurchaseRequestSeeder::class,
            DemoSavingsGoalSeeder::class,
            DemoAllowanceSeeder::class,
        ]);
    }
}