<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * /dashboard sends each user to the right dashboard for their role.
 */
class DashboardController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        return $request->user()->isParent()
            ? redirect()->route('parent.dashboard')
            : redirect()->route('child.dashboard');
    }
}