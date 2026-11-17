<?php

namespace App\Console\Commands;

use App\Services\AllowanceService;
use Illuminate\Console\Command;

class PayDueAllowances extends Command
{
    protected $signature = 'allowances:pay';

    protected $description = 'Pay every scheduled allowance that is due (safe to run many times)';

    public function handle(AllowanceService $allowances): int
    {
        $paid = $allowances->processDue();

        $this->info("Paid {$paid} allowance(s).");

        return self::SUCCESS;
    }
}