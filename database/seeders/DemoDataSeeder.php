<?php

namespace Database\Seeders;

use App\Enums\TransactionSource;
use App\Models\SpendingCategory;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Demo families for development and the final presentation.
 * Every login uses the password: password
 */
class DemoDataSeeder extends Seeder
{
    /** Realistic things a Bangladeshi school kid spends on, by category slug. */
    private const SPENDING = [
        'food' => ['Singara at school canteen', 'Ice cream', 'Fuchka with friends', 'Juice and biscuits', 'Jhalmuri'],
        'education' => ['Notebooks', 'Geometry box', 'Story book', 'Colour pencils', 'Photocopy of class notes'],
        'entertainment' => ['Cricket ball', 'Funfair ride', 'Board game with friends', 'Kite and string'],
        'shopping' => ['Birthday gift for a friend', 'New water bottle', 'Keychain', 'Pencil case'],
        'others' => ['Rickshaw fare', 'Donation at school charity drive', 'Bus fare'],
    ];

    /**
     * Laravel hands us the WalletService automatically.
     */
    public function run(WalletService $walletService): void
    {
        // Fixed "random" seed: every teammate gets IDENTICAL demo data
        mt_srand(2026);

        // ---- Family 1: two parents, two children ----
        $rahim   = $this->makeUser('Rahim Ahmed',   'rahim@demo.com',   'parent');
        $nasreen = $this->makeUser('Nasreen Ahmed', 'nasreen@demo.com', 'parent');
        $ayaan   = $this->makeUser('Ayaan Ahmed',   'ayaan@demo.com',   'child', '2014-03-15');
        $maya    = $this->makeUser('Maya Ahmed',    'maya@demo.com',    'child', '2017-08-22');

        $rahim->children()->attach([$ayaan->id, $maya->id]);
        $nasreen->children()->attach([$ayaan->id, $maya->id]);

        // ---- Family 2: used to test that parents can't see OTHER families' kids ----
        $karim  = $this->makeUser('Karim Hossain',  'karim@demo.com',  'parent');
        $tasnim = $this->makeUser('Tasnim Hossain', 'tasnim@demo.com', 'child', '2012-11-05');

        $karim->children()->attach($tasnim->id);

        // ---- Two months of history, created through the real WalletService ----
        $this->simulateHistory($walletService, $ayaan,  $rahim,   weeklyAllowance: 300);
        $this->simulateHistory($walletService, $maya,   $nasreen, weeklyAllowance: 150);
        $this->simulateHistory($walletService, $tasnim, $karim,   weeklyAllowance: 400);

        // A locked wallet, so Feature 9 has something to show
        $walletService->lock($tasnim->wallet, $karim, 'Paused while we talk about spending');
    }

    private function makeUser(string $name, string $email, string $role, ?string $dateOfBirth = null): User
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => 'password', // hashed automatically by the User model
            'role' => $role,
            'date_of_birth' => $dateOfBirth,
        ]);
    }

    /**
     * Replays ~2 months of family life day by day:
     * a welcome gift, an allowance every Friday, and spending on about 40% of days.
     */
    private function simulateHistory(WalletService $walletService, User $child, User $parent, int $weeklyAllowance): void
    {
        $categories = SpendingCategory::all()->keyBy('slug');
        $slugs = array_keys(self::SPENDING);
        $wallet = $child->wallet;
        $start = now()->subDays(60)->startOfDay();

        // "Time travel": everything created after this line gets this date
        Carbon::setTestNow($start->copy()->setTime(10, 0));
        $walletService->credit($wallet, 500, TransactionSource::PocketMoney, 'Welcome gift', $parent);

        for ($day = 1; $day < 60; $day++) {
            $date = $start->copy()->addDays($day);

            if ($date->isFriday()) {
                Carbon::setTestNow($date->copy()->setTime(9, 0));
                $walletService->credit($wallet, $weeklyAllowance, TransactionSource::Allowance, 'Weekly allowance', $parent);
            }

            if (mt_rand(1, 100) <= 40) {
                $slug = $slugs[mt_rand(0, count($slugs) - 1)];
                $items = self::SPENDING[$slug];
                $description = $items[mt_rand(0, count($items) - 1)];
                $amount = mt_rand(4, 40) * 5; // ৳20 to ৳200, in steps of 5

                // Only spend money the child actually has
                if (bccomp($wallet->balance, (string) $amount, 2) >= 0) {
                    Carbon::setTestNow($date->copy()->setTime(mt_rand(8, 19), mt_rand(0, 59)));
                    $walletService->debit(
                        $wallet,
                        $amount,
                        TransactionSource::Spend,
                        $description,
                        $child,
                        null,
                        $categories[$slug]->id,
                    );
                }
            }
        }

        // Back to the real current time
        Carbon::setTestNow();
    }
}