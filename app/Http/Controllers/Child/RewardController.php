<?php

namespace App\Http\Controllers\Child;

use App\Http\Controllers\Controller;
use App\Services\RewardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RewardController extends Controller
{
    public function index(Request $request, RewardService $rewards): View
    {
        $progress = $rewards->progress($request->user());

        return view('child.rewards', [
            'progress' => $progress,
            'earnedCount' => collect($progress)->whereNotNull('earned')->count(),
        ]);
    }
}