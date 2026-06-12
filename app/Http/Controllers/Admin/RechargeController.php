<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\Router;
use App\Services\BillingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RechargeController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\UserRecharge::with(['customer', 'plan']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('username', 'like', "%{$search}%");
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $recharges = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        return Inertia::render('Admin/Recharges/Index', [
            'recharges' => $recharges,
            'filters' => $request->only(['search', 'type']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Recharges/Create', [
            'customers' => Customer::orderBy('username')->get(),
            'plans' => Plan::where('enabled', 1)->orderBy('name_plan')->get(),
            'routers' => Router::where('enabled', 1)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, BillingService $billing)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:tbl_customers,id',
            'plan_id' => 'required|exists:tbl_plans,id',
            'router_name' => 'required|string',
        ]);

        $adminId = auth()->guard('admin')->id();

        $invoice = $billing->recharge(
            $validated['customer_id'],
            $validated['router_name'],
            $validated['plan_id'],
            'Admin',
            'Manual',
            $adminId,
            'Admin manual recharge'
        );

        if ($invoice) {
            return redirect()->route('admin.recharges.index')->with('success', "Recharge successful. Invoice: {$invoice}");
        }

        return back()->withErrors(['error' => 'Recharge failed']);
    }

    public function destroy(\App\Models\UserRecharge $recharge)
    {
        $recharge->delete();
        return redirect()->route('admin.recharges.index')->with('success', 'Recharge record deleted');
    }
}
