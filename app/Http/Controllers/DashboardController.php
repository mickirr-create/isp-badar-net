<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Plan;
use App\Models\Router;
use App\Models\Transaction;
use App\Models\UserRecharge;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('customer.dashboard');
        }

        $totalCustomers = Customer::count();
        $activeCustomers = UserRecharge::where('status', 'on')->where('expiration', '>=', Carbon::today())->count();
        $totalPlans = Plan::where('enabled', 1)->count();
        $totalRouters = Router::count();
        $onlineRouters = Router::where('status', 'online')->count();

        $totalRevenue = Transaction::where('recharged_on', Carbon::today())->sum('price');
        $monthlyRevenue = Transaction::where('recharged_on', '>=', Carbon::now()->startOfMonth())->sum('price');
        $dailyTransactions = Transaction::where('recharged_on', Carbon::today())->count();

        $recentTransactions = Transaction::with('customer')
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'invoice' => $t->invoice,
                'username' => $t->username,
                'plan_name' => $t->plan_name,
                'price' => $t->price,
                'method' => $t->method,
                'recharged_on' => $t->recharged_on,
            ]);

        $recentCustomers = Customer::orderBy('id', 'desc')->limit(5)->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'username' => $c->username,
                'name' => $c->name,
                'status' => $c->status,
                'created_at' => $c->created_at?->format('Y-m-d'),
            ]);

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalCustomers' => $totalCustomers,
                'activeCustomers' => $activeCustomers,
                'totalPlans' => $totalPlans,
                'totalRouters' => $totalRouters,
                'onlineRouters' => $onlineRouters,
                'todayRevenue' => $totalRevenue,
                'monthlyRevenue' => $monthlyRevenue,
                'dailyTransactions' => $dailyTransactions,
            ],
            'recentTransactions' => $recentTransactions,
            'recentCustomers' => $recentCustomers,
        ]);
    }

    public function customerDashboard(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $activeRecharge = UserRecharge::with('plan')
            ->where('customer_id', $customer->id)
            ->where('status', 'on')
            ->where('expiration', '>=', Carbon::today())
            ->first();

        $recentTransactions = Transaction::where('user_id', $customer->id)
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        $totalSpent = Transaction::where('user_id', $customer->id)->sum('price');

        return Inertia::render('Customer/Dashboard', [
            'customer' => $customer,
            'activeRecharge' => $activeRecharge,
            'recentTransactions' => $recentTransactions,
            'totalSpent' => $totalSpent,
        ]);
    }
}
