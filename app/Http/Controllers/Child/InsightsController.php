<?php

namespace App\Http\Controllers\Child;

use App\Http\Controllers\Controller;
use App\Services\InsightsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InsightsController extends Controller
{
    public function index(Request $request, InsightsService $insights): View
    {
        $period = $insights->periodFrom($request);

        return view('child.insights', [
            'period' => $period,
            'data' => $insights->build($request->user()->wallet, $period),
        ]);
    }
}