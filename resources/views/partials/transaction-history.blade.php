{{--
    Filter bar + totals + list + pagination, shared by the child and parent history pages.
    Needs: $transactions, $summary, $filters, $categories, $indexUrl, $exportUrl
--}}
@php
    $types = \App\Services\TransactionHistoryService::TYPES;
    $weekStart = now()->startOfWeek(\Illuminate\Support\Carbon::SATURDAY);
    $ranges = [
        'This week' => [$weekStart->toDateString(), now()->toDateString()],
        'This month' => [now()->startOfMonth()->toDateString(), now()->toDateString()],
        'Last month' => [now()->subMonthNoOverflow()->startOfMonth()->toDateString(), now()->subMonthNoOverflow()->endOfMonth()->toDateString()],
        'All time' => [null, null],
    ];
    $activeFrom = $filters['from']?->toDateString();
    $activeTo = $filters['to']?->toDateString();
    $queryString = request()->getQueryString();
@endphp

{{-- Quick date ranges --}}
<div class="d-flex flex-wrap gap-2 mb-3">
    @foreach ($ranges as $label => [$from, $to])
        <a href="{{ request()->fullUrlWithQuery(['from' => $from, 'to' => $to, 'page' => null]) }}"
           @class([
               'btn btn-sm rounded-pill px-3',
               'btn-kw' => $activeFrom === $from && $activeTo === $to,
               'btn-light border' => ! ($activeFrom === $from && $activeTo === $to),
           ])>{{ $label }}</a>
    @endforeach
</div>

{{-- Filter form (GET, so filters live in the URL and can be bookmarked) --}}
<form method="GET" action="{{ $indexUrl }}" class="kw-card mb-4">
    <div class="row g-2 align-items-end">
        <div class="col-md-6 col-lg-3">
            <label for="type" class="form-label small fw-medium">Show</label>
            <select id="type" name="type" class="form-select">
                @foreach ($types as $value => $label)
                    <option value="{{ $value }}" @selected($filters['type'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 col-lg-2">
            <label for="category" class="form-label small fw-medium">Category</label>
            <select id="category" name="category" class="form-select">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected($filters['category'] === $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-lg-2">
            <label for="from" class="form-label small fw-medium">From</label>
            <input type="date" id="from" name="from" value="{{ $activeFrom }}" class="form-control">
        </div>
        <div class="col-6 col-lg-2">
            <label for="to" class="form-label small fw-medium">To</label>
            <input type="date" id="to" name="to" value="{{ $activeTo }}" class="form-control">
        </div>
        <div class="col-md-8 col-lg-3">
            <label for="search" class="form-label small fw-medium">Search</label>
            <input type="search" id="search" name="search" value="{{ $filters['search'] }}" maxlength="50"
                   class="form-control" placeholder="e.g. fuchka">
        </div>
        <div class="col-md-4 col-lg-12 d-flex gap-2 justify-content-lg-end">
            <a href="{{ $indexUrl }}" class="btn btn-light border rounded-pill px-3">Reset</a>
            <button type="submit" class="btn btn-kw rounded-pill px-4"><i class="bi bi-funnel"></i> Apply</button>
        </div>
    </div>
</form>

{{-- Totals: income and expenses shown separately --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="kw-card stat-card h-100">
            <div class="stat-label"><i class="bi bi-arrow-down-left text-success"></i> Money in</div>
            <div class="stat-value text-success">৳{{ number_format($summary['income'], 2) }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kw-card stat-card h-100">
            <div class="stat-label"><i class="bi bi-arrow-up-right text-danger"></i> Money out</div>
            <div class="stat-value text-danger">৳{{ number_format($summary['expenses'], 2) }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kw-card stat-card h-100">
            <div class="stat-label"><i class="bi bi-plus-slash-minus"></i> Net</div>
            <div class="stat-value">{{ bccomp($summary['net'], '0', 2) < 0 ? '−' : '' }}৳{{ number_format(abs((float) $summary['net']), 2) }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kw-card stat-card h-100">
            <div class="stat-label"><i class="bi bi-receipt"></i> Transactions</div>
            <div class="stat-value">{{ $summary['count'] }}</div>
        </div>
    </div>
</div>

<div class="kw-card">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
        <h3 class="h6 fw-semibold mb-0">{{ $types[$filters['type']] }}</h3>
        <a href="{{ $exportUrl }}{{ $queryString ? '?'.$queryString : '' }}" class="btn btn-sm btn-light border rounded-pill px-3">
            <i class="bi bi-download"></i> Download CSV
        </a>
    </div>

    @forelse ($transactions as $transaction)
        @include('partials.transaction-row')
    @empty
        <div class="empty-state">
            <i class="bi bi-search"></i>
            <p class="mt-2 mb-0">No transactions match these filters.</p>
        </div>
    @endforelse

    @if ($transactions->hasPages())
        <div class="mt-3">{{ $transactions->links() }}</div>
    @endif
</div>