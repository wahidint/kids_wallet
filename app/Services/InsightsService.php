<?php

namespace App\Services;

use App\Models\SpendingCategory;
use App\Models\Wallet;
use Illuminate\Http\Request;

/**
 * Feature 8: Spending Insights.
 * Turns a wallet's transactions into chart data and plain-language tips.
 */
class InsightsService
{
    public const PERIODS = [
        'month' => 'This month',
        '3m' => 'Last 3 months',
        '6m' => 'Last 6 months',
    ];

    private const PERIOD_MONTHS = ['month' => 1, '3m' => 3, '6m' => 6];

    /** MySQL DAYOFWEEK(): 1 = Sunday ... 7 = Saturday. Shown Saturday-first (Bangladesh week). */
    private const WEEK_ORDER = [7 => 'Sat', 1 => 'Sun', 2 => 'Mon', 3 => 'Tue', 4 => 'Wed', 5 => 'Thu', 6 => 'Fri'];
    private const DAY_NAMES = [7 => 'Saturday', 1 => 'Sunday', 2 => 'Monday', 3 => 'Tuesday', 4 => 'Wednesday', 5 => 'Thursday', 6 => 'Friday'];

    private const SMALL_SPEND = 50;

    public function periodFrom(Request $request): string
    {
        $period = (string) $request->query('period', 'month');

        return array_key_exists($period, self::PERIODS) ? $period : 'month';
    }

    public function build(Wallet $wallet, string $period): array
    {
        $months = self::PERIOD_MONTHS[$period];
        $start = now()->startOfMonth()->subMonths($months - 1);
        $previousStart = $start->copy()->subMonths($months);

        // ---- Totals for this period and the one before it ----
        $expenses = (float) $wallet->transactions()->expenses()->where('created_at', '>=', $start)->sum('amount');
        $income = (float) $wallet->transactions()->income()->where('created_at', '>=', $start)->sum('amount');
        $previousExpenses = (float) $wallet->transactions()->expenses()
            ->where('created_at', '>=', $previousStart)
            ->where('created_at', '<', $start)
            ->sum('amount');

        // ---- Spending by category (one GROUP BY query) ----
        $categoryInfo = SpendingCategory::all()->keyBy('id');

        $categories = $wallet->transactions()->expenses()
            ->where('created_at', '>=', $start)
            ->selectRaw('category_id, SUM(amount) AS total, COUNT(*) AS items')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->get()
            ->map(function ($row) use ($categoryInfo, $expenses) {
                $category = $categoryInfo->get($row->category_id);

                return [
                    'name' => $category->name ?? 'Other',
                    'icon' => $category->icon ?? 'bi-three-dots',
                    'color' => $category->color ?? '#6B7280',
                    'total' => (float) $row->total,
                    'items' => (int) $row->items,
                    'percent' => $expenses > 0 ? (int) round($row->total / $expenses * 100) : 0,
                ];
            })
            ->values()
            ->all();

        // ---- Income vs spending for each of the last 6 months ----
        $sixMonthsStart = now()->startOfMonth()->subMonths(5);
        $monthExpr = "DATE_FORMAT(created_at, '%Y-%m')";

        $incomeByMonth = $wallet->transactions()->income()
            ->where('created_at', '>=', $sixMonthsStart)
            ->selectRaw("{$monthExpr} AS ym, SUM(amount) AS total")
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $expensesByMonth = $wallet->transactions()->expenses()
            ->where('created_at', '>=', $sixMonthsStart)
            ->selectRaw("{$monthExpr} AS ym, SUM(amount) AS total")
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $monthly = ['labels' => [], 'income' => [], 'expenses' => []];
        for ($i = 0; $i < 6; $i++) {
            $month = $sixMonthsStart->copy()->addMonths($i);
            $key = $month->format('Y-m');
            $monthly['labels'][] = $month->format('M');
            $monthly['income'][] = (float) ($incomeByMonth[$key] ?? 0);
            $monthly['expenses'][] = (float) ($expensesByMonth[$key] ?? 0);
        }

        // ---- Spending by day of the week ----
        $byDay = $wallet->transactions()->expenses()
            ->where('created_at', '>=', $start)
            ->selectRaw('DAYOFWEEK(created_at) AS dow, SUM(amount) AS total')
            ->groupBy('dow')
            ->pluck('total', 'dow');

        $weekdays = ['labels' => [], 'totals' => []];
        foreach (self::WEEK_ORDER as $dow => $label) {
            $weekdays['labels'][] = $label;
            $weekdays['totals'][] = (float) ($byDay[$dow] ?? 0);
        }

        // ---- Biggest spends and small spends ----
        $topExpenses = $wallet->transactions()->expenses()
            ->with('category')
            ->where('created_at', '>=', $start)
            ->orderByDesc('amount')
            ->take(5)
            ->get();

        $small = $wallet->transactions()->expenses()
            ->where('created_at', '>=', $start)
            ->where('amount', '<=', self::SMALL_SPEND)
            ->selectRaw('COUNT(*) AS items, COALESCE(SUM(amount), 0) AS total')
            ->first();

        return [
            'periodLabel' => self::PERIODS[$period],
            'income' => $income,
            'expenses' => $expenses,
            'categories' => $categories,
            'monthly' => $monthly,
            'weekdays' => $weekdays,
            'topExpenses' => $topExpenses,
            'insights' => $this->insights(
                $wallet, $income, $expenses, $previousExpenses, $categories, $byDay->all(),
                (int) $small->items, (float) $small->total,
            ),
        ];
    }

    /**
     * Plain-language insights. Each one: [icon, tone (success|warning|info), text].
     */
    private function insights(
        Wallet $wallet, float $income, float $expenses, float $previousExpenses,
        array $categories, array $byDay, int $smallItems, float $smallTotal,
    ): array {
        $tips = [];

        if ($expenses <= 0) {
            $tips[] = ['bi-emoji-smile', 'info', 'No spending recorded in this period yet.'];
        } else {
            // Biggest category
            $top = $categories[0];
            $tips[] = $top['percent'] >= 40
                ? ['bi-pie-chart', 'warning', "{$top['name']} takes {$top['percent']}% of all spending. A weekly budget for it could help."]
                : ['bi-pie-chart', 'info', "Spending is nicely spread out. The biggest category is {$top['name']} at {$top['percent']}%."];

            // Compared with the previous period
            if ($previousExpenses > 0) {
                $change = (int) round(($expenses - $previousExpenses) / $previousExpenses * 100);

                if ($change > 10) {
                    $tips[] = ['bi-graph-up-arrow', 'warning', "Spending is up {$change}% compared with the previous period (৳".number_format($expenses).' vs ৳'.number_format($previousExpenses).').'];
                } elseif ($change < -10) {
                    $tips[] = ['bi-graph-down-arrow', 'success', 'Great job! Spending is down '.abs($change).'% compared with the previous period.'];
                } else {
                    $tips[] = ['bi-dash-circle', 'info', 'Spending is about the same as the previous period.'];
                }
            }

            // Income vs spending
            if ($income > 0) {
                if ($expenses > $income) {
                    $tips[] = ['bi-exclamation-triangle', 'warning', 'Spending (৳'.number_format($expenses).') was more than the money received (৳'.number_format($income).').'];
                } else {
                    $kept = (int) round(($income - $expenses) / $income * 100);
                    $tips[] = ['bi-piggy-bank', 'success', "{$kept}% of the money received was not spent. Keep it up!"];
                }
            }

            // Busiest day
            if (! empty($byDay)) {
                $busiestDow = array_search(max($byDay), $byDay);
                $tips[] = ['bi-calendar-week', 'info', 'Most spending happens on '.self::DAY_NAMES[$busiestDow].'s (৳'.number_format((float) $byDay[$busiestDow]).').'];
            }

            // Small spends add up
            if ($smallItems >= 5) {
                $tips[] = ['bi-coin', 'info', "{$smallItems} small buys (৳".self::SMALL_SPEND.' or less) added up to ৳'.number_format($smallTotal).'. Small spends add up!'];
            }
        }

        // Spending limits close to the edge (F2)
        foreach ($wallet->limitUsage() as $row) {
            if ($row['status'] !== 'ok') {
                $tips[] = ['bi-speedometer2', 'warning', "{$row['label']}'s spending limit is {$row['percent']}% used."];
            }
        }

        return $tips;
    }
}