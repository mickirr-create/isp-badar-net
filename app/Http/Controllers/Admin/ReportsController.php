<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\UserRecharge;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::query();

        if ($request->filled('date_from')) {
            $query->where('recharged_on', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('recharged_on', '<=', $request->date_to);
        }
        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $transactions = $query->orderBy('id', 'desc')->paginate(25)->withQueryString();

        $totalIncome = (clone $query)->sum('price');
        $totalCount = (clone $query)->count();

        return Inertia::render('Admin/Reports/Index', [
            'transactions' => $transactions,
            'stats' => [
                'totalIncome' => $totalIncome,
                'totalCount' => $totalCount,
            ],
            'filters' => $request->only(['date_from', 'date_to', 'method', 'type']),
        ]);
    }

    public function dailyReport(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::today()->toDateString());
        $dateTo = $request->input('date_to', Carbon::today()->toDateString());

        $transactions = Transaction::whereBetween('recharged_on', [$dateFrom, $dateTo])
            ->orderBy('recharged_on', 'desc')
            ->get();

        $totalIncome = $transactions->sum('price');

        return Inertia::render('Admin/Reports/Daily', [
            'transactions' => $transactions,
            'totalIncome' => $totalIncome,
            'filters' => ['date_from' => $dateFrom, 'date_to' => $dateTo],
        ]);
    }

    public function chartData(Request $request)
    {
        $days = $request->integer('days', 30);

        $dailyData = Transaction::where('recharged_on', '>=', Carbon::now()->subDays($days))
            ->selectRaw('DATE(recharged_on) as date, SUM(price) as total, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $byType = Transaction::where('recharged_on', '>=', Carbon::now()->subDays($days))
            ->selectRaw('type, SUM(price) as total, COUNT(*) as count')
            ->groupBy('type')
            ->get();

        $byMethod = Transaction::where('recharged_on', '>=', Carbon::now()->subDays($days))
            ->selectRaw('method, SUM(price) as total, COUNT(*) as count')
            ->groupBy('method')
            ->get();

        return response()->json([
            'daily' => $dailyData,
            'byType' => $byType,
            'byMethod' => $byMethod,
        ]);
    }

    public function activePlans()
    {
        $activePlans = UserRecharge::with(['customer', 'plan'])
            ->where('status', 'on')
            ->where('expiration', '>=', Carbon::today())
            ->orderBy('expiration')
            ->paginate(25);

        $expiredPlans = UserRecharge::with(['customer', 'plan'])
            ->where('status', 'on')
            ->where('expiration', '<', Carbon::today())
            ->orderBy('expiration', 'desc')
            ->paginate(25);

        return Inertia::render('Admin/Reports/ActivePlans', [
            'activePlans' => $activePlans,
            'expiredPlans' => $expiredPlans,
        ]);
    }
}
