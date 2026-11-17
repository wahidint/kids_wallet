<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\InsightsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * A parent's view of one child's insights.
 * The route's "can:manage-child" check runs before this.
 */
class ChildInsightsController extends Controller
{
    public function index(Request $request, User $child, InsightsService $insights): View
    {
        $period = $insights->periodFrom($request);

        return view('parent.children.insights', [
            'child' => $child,
            'period' => $period,
            'data' => $insights->build($child->wallet, $period),
        ]);
    }
}