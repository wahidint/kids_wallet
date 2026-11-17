{{-- Insights page body, shared by child and parent. Needs: $data, $period, $baseUrl --}}
@php
    $periods = \App\Services\InsightsService::PERIODS;
    $topCategory = $data['categories'][0] ?? null;
    $chartData = [
        'categories' => $data['categories'],
        'monthly' => $data['monthly'],
        'weekdays' => $data['weekdays'],
    ];
    $toneClass = ['success' => 'is-success', 'warning' => 'is-warning', 'info' => 'is-info'];
@endphp

{{-- Period switch --}}
<div class="d-flex flex-wrap gap-2 mb-4">
    @foreach ($periods as $value => $label)
        <a href="{{ $baseUrl }}?period={{ $value }}"
           @class(['btn btn-sm rounded-pill px-3', 'btn-kw' => $period === $value, 'btn-light border' => $period !== $value])>
            {{ $label }}
        </a>
    @endforeach
</div>

{{-- Headline numbers --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="kw-card stat-card h-100 d-flex align-items-center gap-3">
            <div class="stat-icon text-success" style="background:#DCFCE7;"><i class="bi bi-arrow-down-left"></i></div>
            <div>
                <div class="stat-label">Money in · {{ $data['periodLabel'] }}</div>
                <div class="stat-value">৳{{ number_format($data['income'], 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kw-card stat-card h-100 d-flex align-items-center gap-3">
            <div class="stat-icon text-danger" style="background:#FEE2E2;"><i class="bi bi-arrow-up-right"></i></div>
            <div>
                <div class="stat-label">Spent · {{ $data['periodLabel'] }}</div>
                <div class="stat-value">৳{{ number_format($data['expenses'], 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kw-card stat-card h-100 d-flex align-items-center gap-3">
            <div class="stat-icon"><i class="bi {{ $topCategory['icon'] ?? 'bi-pie-chart' }}"></i></div>
            <div>
                <div class="stat-label">Biggest category</div>
                <div class="stat-value">{{ $topCategory ? $topCategory['name'].' · '.$topCategory['percent'].'%' : '-' }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- Where the money went --}}
    <div class="col-lg-5">
        <div class="kw-card h-100">
            <h3 class="h6 fw-semibold mb-3">Where the money went</h3>
            @if (empty($data['categories']))
                <div class="empty-state">
                    <i class="bi bi-pie-chart"></i>
                    <p class="mt-2 mb-0">No spending in this period yet.</p>
                </div>
            @else
                <div class="doughnut-wrap">
                    <canvas id="categoryChart" aria-label="Spending by category chart" role="img"></canvas>
                    <div class="doughnut-center">
                        <div class="small text-secondary">Spent</div>
                        <div class="fw-bold">৳{{ number_format($data['expenses']) }}</div>
                    </div>
                </div>
                <div class="mt-3">
                    @foreach ($data['categories'] as $category)
                        <div class="d-flex align-items-center gap-2 small py-1">
                            <span class="legend-dot" style="background: {{ $category['color'] }};"></span>
                            <span class="flex-grow-1">{{ $category['name'] }}
                                <span class="text-secondary">· {{ $category['items'] }} {{ \Illuminate\Support\Str::plural('buy', $category['items']) }}</span>
                            </span>
                            <span class="fw-semibold">৳{{ number_format($category['total'], 2) }}</span>
                            <span class="text-secondary" style="width: 3rem; text-align: right;">{{ $category['percent'] }}%</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Smart insights --}}
    <div class="col-lg-7">
        <div class="kw-card h-100">
            <h3 class="h6 fw-semibold mb-3"><i class="bi bi-lightbulb text-kw"></i> Insights and suggestions</h3>
            @foreach ($data['insights'] as [$icon, $tone, $text])
                <div class="insight-item {{ $toneClass[$tone] }}">
                    <i class="bi {{ $icon }}"></i>
                    <div>{{ $text }}</div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <div class="kw-card h-100">
            <h3 class="h6 fw-semibold mb-3">Money in vs spending · last 6 months</h3>
            <div class="chart-wrap"><canvas id="monthlyChart" aria-label="Monthly income and spending chart" role="img"></canvas></div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="kw-card h-100">
            <h3 class="h6 fw-semibold mb-3">Spending by day · {{ strtolower($data['periodLabel']) }}</h3>
            <div class="chart-wrap"><canvas id="weekdayChart" aria-label="Spending by weekday chart" role="img"></canvas></div>
        </div>
    </div>
</div>

<div class="kw-card">
    <h3 class="h6 fw-semibold mb-2">Biggest spends · {{ strtolower($data['periodLabel']) }}</h3>
    @forelse ($data['topExpenses'] as $transaction)
        @include('partials.transaction-row')
    @empty
        <p class="text-secondary small mb-0">Nothing spent in this period.</p>
    @endforelse
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        const insightsData = @json($chartData);
        const taka = (value) => '৳' + Number(value).toLocaleString('en-US', { maximumFractionDigits: 2 });

        Chart.defaults.font.family = "'Poppins', 'Hind Siliguri', sans-serif";
        Chart.defaults.color = '#6B7280';

        // 1. Where the money went (doughnut)
        const categoryCanvas = document.getElementById('categoryChart');
        if (categoryCanvas) {
            new Chart(categoryCanvas, {
                type: 'doughnut',
                data: {
                    labels: insightsData.categories.map((c) => c.name),
                    datasets: [{
                        data: insightsData.categories.map((c) => c.total),
                        backgroundColor: insightsData.categories.map((c) => c.color),
                        borderColor: '#fff',
                        borderWidth: 3,
                        hoverOffset: 8,
                    }],
                },
                options: {
                    cutout: '68%',
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: (ctx) => ` ${ctx.label}: ${taka(ctx.parsed)}` } },
                    },
                },
            });
        }

        // 2. Money in vs spending per month (grouped bars)
        new Chart(document.getElementById('monthlyChart'), {
            type: 'bar',
            data: {
                labels: insightsData.monthly.labels,
                datasets: [
                    { label: 'Money in', data: insightsData.monthly.income, backgroundColor: '#16A34A', borderRadius: 6 },
                    { label: 'Spending', data: insightsData.monthly.expenses, backgroundColor: '#E2136E', borderRadius: 6 },
                ],
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { callbacks: { label: (ctx) => ` ${ctx.dataset.label}: ${taka(ctx.parsed.y)}` } },
                },
                scales: {
                    y: { beginAtZero: true, ticks: { callback: (value) => taka(value) } },
                    x: { grid: { display: false } },
                },
            },
        });

        // 3. Spending by weekday (busiest day highlighted)
        const dayTotals = insightsData.weekdays.totals;
        const busiest = Math.max(...dayTotals);
        new Chart(document.getElementById('weekdayChart'), {
            type: 'bar',
            data: {
                labels: insightsData.weekdays.labels,
                datasets: [{
                    label: 'Spending',
                    data: dayTotals,
                    backgroundColor: dayTotals.map((v) => (v === busiest && v > 0 ? '#7C3AED' : '#DDD6FE')),
                    borderRadius: 6,
                }],
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (ctx) => ` ${taka(ctx.parsed.y)}` } },
                },
                scales: {
                    y: { beginAtZero: true, ticks: { callback: (value) => taka(value) } },
                    x: { grid: { display: false } },
                },
            },
        });
    </script>
@endpush