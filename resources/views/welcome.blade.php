@section('title', 'Smart pocket money for kids')
<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
</head>
<body class="landing">

<nav class="container d-flex justify-content-between align-items-center py-4">
    <a href="{{ route('home') }}" class="brand text-decoration-none">
        <span class="brand-mark"><i class="bi bi-wallet2"></i></span> {{ config('app.name') }}
    </a>
    <div class="d-flex gap-2">
        @auth
            <a href="{{ route('dashboard') }}" class="btn btn-kw rounded-pill px-4">Go to dashboard</a>
        @else
            <a href="{{ route('login') }}" class="btn btn-light border rounded-pill px-4">Log in</a>
            <a href="{{ route('register') }}" class="btn btn-kw rounded-pill px-4 d-none d-sm-inline-block">Create account</a>
        @endauth
    </div>
</nav>

<header class="container py-4 py-lg-5">
    <div class="row align-items-center g-5">
        <div class="col-lg-6">
            <span class="badge rounded-pill bg-white text-kw border px-3 py-2 mb-3">
                <i class="bi bi-stars"></i> Financial literacy for under-16s
            </span>
            <h1 class="display-4 fw-bold lh-sm">Pocket money that teaches <span class="text-kw">smart habits</span>.</h1>
            <p class="lead text-secondary my-4">
                A bKash-inspired digital wallet where children save, spend and learn, and parents stay in control.
            </p>
            <div class="d-flex flex-wrap gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-kw btn-lg rounded-pill px-5">Open my wallet</a>
                @else
                    <a href="{{ route('register') }}" class="btn btn-kw btn-lg rounded-pill px-5">Get started</a>
                    <a href="{{ route('register', ['role' => 'child']) }}" class="btn btn-light border btn-lg rounded-pill px-4">
                        <i class="bi bi-emoji-smile"></i> I'm a kid
                    </a>
                @endauth
            </div>
        </div>
        <div class="col-lg-6">
            <div class="hero-visual">
                <i class="bi bi-wallet2"></i>
                <span class="hero-chip chip-1"><i class="bi bi-piggy-bank"></i> Save</span>
                <span class="hero-chip chip-2"><i class="bi bi-bag-check"></i> Spend wisely</span>
                <span class="hero-chip chip-3"><i class="bi bi-trophy"></i> Earn badges</span>
            </div>
        </div>
    </div>
</header>

<section class="container py-5">
    <h2 class="h3 fw-bold text-center mb-4">Everything a young saver needs</h2>
    <div class="row g-4">
        @foreach ([
            ['bi-cash-coin', 'Digital pocket money', 'Parents add money instantly; children always see their balance.'],
            ['bi-speedometer2', 'Smart spending limits', 'Daily, weekly and monthly limits keep spending on track.'],
            ['bi-piggy-bank', 'Savings goals', 'Set a goal, save toward it, and watch the progress bar fill up.'],
            ['bi-patch-check', 'Purchase approval', 'Children request, parents approve, money moves only once.'],
            ['bi-pie-chart', 'Spending insights', 'Colourful charts show where the money goes each month.'],
            ['bi-mortarboard', 'Learning games', 'Fun quizzes about saving, budgeting and spending wisely.'],
        ] as [$icon, $title, $text])
            <div class="col-md-6 col-lg-4">
                <div class="kw-card h-100">
                    <div class="feature-icon"><i class="bi {{ $icon }}"></i></div>
                    <h3 class="h5 fw-semibold mt-3">{{ $title }}</h3>
                    <p class="text-secondary mb-0">{{ $text }}</p>
                </div>
            </div>
        @endforeach
    </div>
</section>

<footer class="container py-4 text-center text-secondary small">
    {{ config('app.name') }} · Academic simulation project: no real money is transferred.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>