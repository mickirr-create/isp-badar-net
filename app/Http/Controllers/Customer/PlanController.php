<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        $query = Plan::with('bandwidth')->where('enabled', 1);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $plans = $query->orderBy('name_plan')->get();

        return Inertia::render('Customer/Plans', [
            'plans' => $plans,
            'filters' => $request->only(['type']),
        ]);
    }
}
