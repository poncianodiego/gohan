<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $servers = $request->user()->servers()
            ->withCount('sites')
            ->latest()
            ->get();

        return Inertia::render('Dashboard', [
            'servers' => $servers,
        ]);
    }
}
