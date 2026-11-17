<?php

namespace App\Http\Middleware;

use App\Enums\Badge;
use App\Services\RewardService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Feature 10: whenever a child opens a page, check for newly earned badges
 * and show a "New badge unlocked!" celebration on that page.
 */
class AwardBadges
{
    public function __construct(
        private RewardService $rewards,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($request->isMethod('GET') && $user?->isChild()) {
            $new = $this->rewards->evaluate($user);

            if ($new) {
                // now() = available on THIS page only (not carried to the next)
                $request->session()->now('new_badges', array_map(
                    fn (Badge $badge) => ['label' => $badge->label(), 'icon' => $badge->icon(), 'color' => $badge->color()],
                    $new,
                ));
            }
        }

        return $next($request);
    }
}