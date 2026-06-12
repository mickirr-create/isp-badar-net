<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Router;
use App\Models\Voucher;
use App\Services\VoucherService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VoucherController extends Controller
{
    public function index(Request $request)
    {
        $query = Voucher::with('plan');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('code', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $vouchers = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        return Inertia::render('Admin/Vouchers/Index', [
            'vouchers' => $vouchers,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Vouchers/Create', [
            'plans' => Plan::where('enabled', 1)->orderBy('name_plan')->get(),
            'routers' => Router::where('enabled', 1)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, VoucherService $voucherService)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:tbl_plans,id',
            'router_name' => 'required|string',
            'quantity' => 'required|integer|min:1|max:100',
        ]);

        $adminId = auth()->guard('admin')->id();

        $vouchers = $voucherService->generate(
            $validated['plan_id'],
            $validated['router_name'],
            $validated['quantity'],
            $adminId
        );

        return redirect()->route('admin.vouchers.index')->with('success', count($vouchers) . ' vouchers generated');
    }

    public function destroy(Voucher $voucher)
    {
        $voucher->delete();
        return redirect()->route('admin.vouchers.index')->with('success', 'Voucher deleted');
    }
}
