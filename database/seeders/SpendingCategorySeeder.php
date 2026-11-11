<?php

namespace Database\Seeders;

use App\Models\SpendingCategory;
use Illuminate\Database\Seeder;

class SpendingCategorySeeder extends Seeder
{
    /**
     * The five expense categories required by Feature 5.
     * icon = Bootstrap Icons class, color = used by the charts in Feature 8.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Food',          'slug' => 'food',          'icon' => 'bi-cup-hot',    'color' => '#F59E0B'],
            ['name' => 'Education',     'slug' => 'education',     'icon' => 'bi-book',       'color' => '#3B82F6'],
            ['name' => 'Entertainment', 'slug' => 'entertainment', 'icon' => 'bi-controller', 'color' => '#8B5CF6'],
            ['name' => 'Shopping',      'slug' => 'shopping',      'icon' => 'bi-bag',        'color' => '#E2136E'],
            ['name' => 'Others',        'slug' => 'others',        'icon' => 'bi-three-dots', 'color' => '#6B7280'],
        ];

        foreach ($categories as $category) {
            // Create it, or update it if it already exists: safe to run many times
            SpendingCategory::updateOrCreate(['slug' => $category['slug']], $category);
        }
    }
}