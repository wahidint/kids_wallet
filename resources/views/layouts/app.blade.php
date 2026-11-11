<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
</head>
<body class="{{ auth()->user()->isParent() ? 'theme-parent' : 'theme-child' }}">
<div class="app-shell">
    {{-- Desktop sidebar --}}
    <aside class="sidebar d-none d-lg-flex">
        @include('layouts.navigation')
    </aside>

    {{-- Mobile slide-in menu --}}
    <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileNav" aria-label="Menu">
        <div class="offcanvas-body d-flex flex-column">
            @include('layouts.navigation')
        </div>
    </div>

    <div class="main">
        <header class="topbar">
            <button class="btn btn-light d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNav" aria-label="Open menu">
                <i class="bi bi-list fs-5"></i>
            </button>
            <h1 class="h5 fw-semibold mb-0 me-auto">@yield('title')</h1>
            <span class="text-secondary small d-none d-sm-inline">{{ now()->format('l, j F Y') }}</span>
        </header>

        <main class="content">
            @include('partials.flash')
            @yield('content')
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>