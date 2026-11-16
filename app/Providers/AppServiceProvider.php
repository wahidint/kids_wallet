<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Page links ("1 2 3 Next") styled with Bootstrap instead of Tailwind
        Paginator::useBootstrapFive();

        // THE security rule for every parent feature:
        // a parent may manage a child only if that child is linked to them.
        Gate::define('manage-child', function (User $parent, User $child) {
            return $parent->isParent() && $parent->isParentOf($child);
        });
    }
}