@php
    $user = auth()->user();
    $pendingApprovals = $user->isParent()
        ? \App\Models\PurchaseRequest::forParent($user)->pending()->count()
        : 0;
    $readyToBuy = $user->isChild()
        ? \App\Models\PurchaseRequest::where('requested_by', $user->id)->approved()->count()
        : 0;
@endphp

<a href="{{ route('dashboard') }}" class="brand text-decoration-none mb-4 px-2">
    <span class="brand-mark"><i class="bi bi-wallet2"></i></span> {{ config('app.name') }}
</a>

<nav class="nav flex-column gap-1 flex-grow-1">
    @if ($user->isParent())
        <a href="{{ route('parent.dashboard') }}" @class(['nav-link', 'active' => request()->routeIs('parent.dashboard', 'parent.children.*')])>
            <i class="bi bi-grid-1x2"></i> Dashboard
        </a>
        <a href="{{ route('parent.purchases.index') }}" @class(['nav-link', 'active' => request()->routeIs('parent.purchases.*')])>
            <i class="bi bi-patch-check"></i> Purchase requests
            @if ($pendingApprovals > 0)
                <span class="badge rounded-pill text-bg-warning ms-auto">{{ $pendingApprovals }}</span>
            @endif
        </a>
    @else
        <a href="{{ route('child.dashboard') }}" @class(['nav-link', 'active' => request()->routeIs('child.dashboard')])>
            <i class="bi bi-house-heart"></i> My Wallet
        </a>
        <a href="{{ route('child.spend.create') }}" @class(['nav-link', 'active' => request()->routeIs('child.spend.*')])>
            <i class="bi bi-bag-check"></i> Spend money
        </a>
        <a href="{{ route('child.goals.index') }}" @class(['nav-link', 'active' => request()->routeIs('child.goals.*')])>
            <i class="bi bi-piggy-bank"></i> Savings goals
        </a>
        <a href="{{ route('child.purchases.index') }}" @class(['nav-link', 'active' => request()->routeIs('child.purchases.*')])>
            <i class="bi bi-patch-question"></i> Ask to buy
            @if ($readyToBuy > 0)
                <span class="badge rounded-pill text-bg-info ms-auto">{{ $readyToBuy }}</span>
            @endif
        </a>
        <a href="{{ route('child.transactions.index') }}" @class(['nav-link', 'active' => request()->routeIs('child.transactions.*')])>
            <i class="bi bi-clock-history"></i> History
        </a>
        <a href="{{ route('child.insights') }}" @class(['nav-link', 'active' => request()->routeIs('child.insights')])>
            <i class="bi bi-pie-chart"></i> Insights
        </a>
    @endif
</nav>

<div class="user-chip mt-4">
    <div class="avatar">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</div>
    <div class="small lh-sm min-w-0">
        <div class="fw-semibold text-truncate">{{ $user->name }}</div>
        <div class="text-secondary">{{ $user->role->label() }}</div>
    </div>
</div>

<form method="POST" action="{{ route('logout') }}" class="mt-2">
    @csrf
    <button type="submit" class="nav-link w-100 text-start border-0 bg-transparent">
        <i class="bi bi-box-arrow-right"></i> Log out
    </button>
</form>