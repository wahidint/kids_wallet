<?php

namespace Database\Seeders;

use App\Models\SpendingCategory;
use App\Models\User;
use App\Services\PurchaseRequestService;
use Illuminate\Database\Seeder;

/**
 * Demo requests in every status, created through the real service.
 */
class DemoPurchaseRequestSeeder extends Seeder
{
    public function run(PurchaseRequestService $purchases): void
    {
        $rahim = User::where('email', 'rahim@demo.com')->firstOrFail();
        $nasreen = User::where('email', 'nasreen@demo.com')->firstOrFail();
        $ayaan = User::where('email', 'ayaan@demo.com')->firstOrFail();
        $maya = User::where('email', 'maya@demo.com')->firstOrFail();

        $category = SpendingCategory::pluck('id', 'slug');

        // Pending: waiting for Rahim or Nasreen
        $purchases->submit($ayaan, [
            'item_name' => 'Cricket bat',
            'amount' => 1200,
            'category_id' => $category['entertainment'],
            'description' => 'For the inter-school cricket tournament next month.',
        ]);

        // Rejected, with a kind note
        $game = $purchases->submit($ayaan, [
            'item_name' => 'Video game',
            'amount' => 3500,
            'category_id' => $category['entertainment'],
            'description' => 'All my friends are playing it.',
        ]);
        $purchases->reject($game, $rahim, "Too expensive for now. Let's make a savings goal for it together!");

        // Approved but Maya doesn't have enough money yet
        $book = $purchases->submit($maya, [
            'item_name' => 'Thakurmar Jhuli story book',
            'amount' => 250,
            'category_id' => $category['education'],
            'description' => 'I want to read it before bed.',
        ]);
        $purchases->approve($book, $nasreen, 'Great choice, Maya!');

        // Completed: approved and bought
        $bag = $purchases->submit($ayaan, [
            'item_name' => 'School bag',
            'amount' => 650,
            'category_id' => $category['education'],
            'description' => 'My old bag is torn.',
        ]);
        $purchases->approve($bag, $rahim);
        $purchases->complete($bag, $ayaan);
    }
}