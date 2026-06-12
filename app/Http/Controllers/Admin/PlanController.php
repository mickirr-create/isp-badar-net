<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bandwidth;
use App\Models\Plan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        $query = Plan::with('bandwidth');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name_plan', 'like', "%{$search}%");
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $plans = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        return Inertia::render('Admin/Plans/Index', [
            'plans' => $plans,
            'filters' => $request->only(['search', 'type']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Plans/Create', [
            'bandwidths' => Bandwidth::orderBy('name_bw')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_plan' => 'required|string|max:100',
            'id_bw' => 'required|exists:tbl_bandwidth,id',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'validity' => 'required|integer|min:1',
            'validity_unit' => 'required|in:Months,Period,Days,Hrs,Mins',
            'expired_date' => 'nullable|integer|min:1',
            'enabled' => 'nullable|boolean',
            'is_radius' => 'nullable|boolean',
            'pool' => 'nullable|string',
            'type' => 'required|in:Hotspot,PPPoE,Balance',
            'device' => 'nullable|string',
        ]);

        $validated['price_format'] = number_format($validated['price'], 0, ',', '.');

        if (isset($validated['enabled'])) {
            $validated['enabled'] = 1;
        }

        Plan::create($validated);

        return redirect()->route('admin.plans.index')->with('success', 'Plan created successfully');
    }

    public function edit(Plan $plan)
    {
        $plan->load('bandwidth');

        return Inertia::render('Admin/Plans/Edit', [
            'plan' => $plan,
            'bandwidths' => Bandwidth::orderBy('name_bw')->get(),
        ]);
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name_plan' => 'required|string|max:100',
            'id_bw' => 'required|exists:tbl_bandwidth,id',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'validity' => 'required|integer|min:1',
            'validity_unit' => 'required|in:Months,Period,Days,Hrs,Mins',
            'expired_date' => 'nullable|integer|min:1',
            'enabled' => 'nullable|boolean',
            'is_radius' => 'nullable|boolean',
            'pool' => 'nullable|string',
            'type' => 'required|in:Hotspot,PPPoE,Balance',
            'device' => 'nullable|string',
        ]);

        $validated['price_format'] = number_format($validated['price'], 0, ',', '.');
        $validated['enabled'] = isset($validated['enabled']) ? 1 : 0;

        $plan->update($validated);

        return redirect()->route('admin.plans.index')->with('success', 'Plan updated successfully');
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();
        return redirect()->route('admin.plans.index')->with('success', 'Plan deleted successfully');
    }
}
