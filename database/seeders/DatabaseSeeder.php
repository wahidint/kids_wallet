<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Runs when you type: php artisan db:seed (or migrate:fresh --seed).
     * Order matters: demo spending needs the categories to exist first.
     */
    public function run(): void
    {
        $this->call([
            SpendingCategorySeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}