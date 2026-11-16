<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Runs when you type: php artisan db:seed (or migrate:fresh --seed).
     * Order matters: demo spending needs the categories to exist first,
     * and limits are added after the history so old spending isn't blocked.
     */
    public function run(): void
    {
        $this->call([
            SpendingCategorySeeder::class,
            DemoDataSeeder::class,
            DemoSpendingLimitSeeder::class,
            DemoPurchaseRequestSeeder::class,
        ]);
    }
}