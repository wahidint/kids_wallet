<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
</head>
<body>
<div class="auth-wrap">
    <section class="auth-hero">
        <a href="{{ route('home') }}" class="brand text-decoration-none">
            <span class="brand-mark"><i class="bi bi-wallet2"></i></span> {{ config('app.name') }}
        </a>

        <div class="my-auto py-5">
            <h2 class="display-6 fw-bold mb-3">Smart money habits start early.</h2>
            <p class="opacity-75 mb-4">Pocket money, savings goals and spending limits, with parents always in the loop.</p>
            <ul class="list-unstyled d-grid gap-3 mb-0">
                <li><i class="bi bi-piggy-bank me-2"></i> Save toward goals and earn badges</li>
                <li><i class="bi bi-shield-check me-2"></i> Parents approve purchases and set limits</li>
                <li><i class="bi bi-mortarboard me-2"></i> Learn money skills through quizzes</li>
            </ul>
        </div>

        <p class="small opacity-75 mb-0">Academic simulation: no real money is transferred.</p>
    </section>

    <main class="auth-form">
        <div class="auth-card">
            <a href="{{ route('home') }}" class="brand auth-mobile-brand text-decoration-none mb-4">
                <span class="brand-mark"><i class="bi bi-wallet2"></i></span> {{ config('app.name') }}
            </a>
            @include('partials.flash')
            @yield('content')
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>