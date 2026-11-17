<?php

namespace App\Services;

use App\Enums\TransactionSource;
use App\Enums\TransactionType;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

/**
 * Feature 5: Smart Expense Tracker.
 * Filters, totals and CSV export for a wallet's transaction history.
 * Used by both the child's and the parent's history pages.
 */
class TransactionHistoryService
{
    public const TYPES = [
        'all' => 'All transactions',
        'income' => 'Income',
        'expenses' => 'Expenses',
        'savings' => 'Savings moves',
    ];

    /**
     * Read filters from the URL safely. Anything invalid is simply ignored,
     * so a mistyped link never causes an error page.
     */
    public function filtersFrom(Request $request): array
    {
        $type = (string) $request->query('type', 'all');
        if (! array_key_exists($type, self::TYPES)) {
            $type = 'all';
        }

        $from = $this->parseDate($request->query('from'));
        $to = $this->parseDate($request->query('to'));

        if ($from && $to && $from->gt($to)) {
            [$from, $to] = [$to, $from]; // dates in the wrong order: swap them
        }

        return [
            'type' => $type,
            'category' => (int) $request->query('category') ?: null,
            'from' => $from,
            'to' => $to,
            'search' => Str::limit(trim((string) $request->query('search')), 50, ''),
        ];
    }

    /** The wallet's transactions with all filters applied (not yet ordered). */
    public function query(Wallet $wallet, array $filters)
    {
        $query = $wallet->transactions()->with('category');

        if ($filters['type'] === 'income') {
            $query->income();
        } elseif ($filters['type'] === 'expenses') {
            $query->expenses();
        } elseif ($filters['type'] === 'savings') {
            $query->whereIn('source', [
                TransactionSource::SavingsDeposit->value,
                TransactionSource::SavingsWithdrawal->value,
            ]);
        }

        if ($filters['category']) {
            $query->where('category_id', $filters['category']);
        }

        if ($filters['from']) {
            $query->where('created_at', '>=', $filters['from']);
        }

        if ($filters['to']) {
            $query->where('created_at', '<', $filters['to']->copy()->addDay()); // include the whole "to" day
        }

        if ($filters['search'] !== '') {
            // Escape % and _ so they are searched for literally
            $query->where('description', 'like', '%'.addcslashes($filters['search'], '%_\\').'%');
        }

        return $query;
    }

    /** Totals for the filtered transactions: income and expenses shown separately. */
    public function summary($query): array
    {
        $income = bcadd((string) (clone $query)->income()->sum('amount'), '0', 2);
        $expenses = bcadd((string) (clone $query)->expenses()->sum('amount'), '0', 2);

        return [
            'income' => $income,
            'expenses' => $expenses,
            'net' => bcsub($income, $expenses, 2),
            'count' => (clone $query)->count(),
        ];
    }

    /** Download the filtered transactions as a CSV file (opens in Excel / Google Sheets). */
    public function csv($query, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');

            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 marker so Excel shows Bangla text correctly

            fputcsv($out, ['Date', 'Description', 'Direction', 'Type', 'Category', 'Amount (BDT)', 'Balance after (BDT)']);

            (clone $query)->orderBy('created_at')->orderBy('id')->chunk(200, function ($transactions) use ($out) {
                foreach ($transactions as $transaction) {
                    $isCredit = $transaction->type === TransactionType::Credit;

                    fputcsv($out, [
                        $transaction->created_at->format('Y-m-d H:i'),
                        $this->safeCell($transaction->description ?? ''),
                        $isCredit ? 'Money in' : 'Money out',
                        $transaction->source->label(),
                        $transaction->category?->name ?? '',
                        ($isCredit ? '' : '-').$transaction->amount,
                        $transaction->balance_after,
                    ]);
                }
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Stops "CSV injection": text starting with = + - @ could run as a
     * formula when the file is opened in Excel.
     */
    private function safeCell(string $value): string
    {
        return preg_match('/^[=+\-@]/', $value) ? "'".$value : $value;
    }
}