<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\UserRecharge;
use App\Services\BillingCycleService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BillingCycleController extends Controller
{
    public function __construct(
        private BillingCycleService $billingCycle,
        private NotificationService $notification
    ) {}

    public function index(Request $request)
    {
        $query = Customer::with(['recharges' => function ($q) {
            $q->where('status', 'on');
        }])->where('billing_day', '>=', 1);

        if ($request->filled('status')) {
            $query->where(function ($q) use ($request) {
                $status = $request->status;
                if ($status === 'due_soon') {
                    // Will filter after query
                } elseif ($status === 'overdue') {
                    $q->whereHas('recharges', function ($rq) {
                        $rq->where('status', 'on')->where('expiration', '<', now());
                    });
                } elseif ($status === 'active') {
                    $q->whereHas('recharges', function ($rq) {
                        $rq->where('status', 'on')->where('expiration', '>=', now());
                    });
                }
            });
        }

        $customers = $query->orderBy('billing_day')->paginate(25)->withQueryString();

        // Add computed fields
        $customers->getCollection()->transform(function ($customer) {
            $customer->billing_status = $this->billingCycle->getBillingStatus($customer);
            $customer->due_date = $this->billingCycle->getDueDate($customer)?->format('Y-m-d');
            $customer->days_until_due = $customer->due_date
                ? (int) now()->diffInDays($customer->due_date, false)
                : null;
            return $customer;
        });

        return Inertia::render('Admin/BillingCycles/Index', [
            'customers' => $customers,
            'filters' => $request->only(['status']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:tbl_customers,id',
            'billing_day' => 'required|integer|min:1|max:28',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);
        $customer->update([
            'billing_day' => $validated['billing_day'],
            'throttle_enabled' => true,
        ]);

        return back()->with('success', 'Billing cycle berhasil diatur');
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'billing_day' => 'required|integer|min:1|max:28',
            'throttle_enabled' => 'required|boolean',
            'throttle_profile' => 'nullable|string|max:100',
        ]);

        $customer->update($validated);

        return back()->with('success', 'Billing cycle berhasil diperbarui');
    }

    public function sendNotification(Request $request, Customer $customer)
    {
        $recharge = $customer->recharges()
            ->where('status', 'on')
            ->first();

        if (!$recharge) {
            return back()->with('error', 'Tidak ada paket aktif');
        }

        $this->notification->sendDueSoonNotification($recharge);

        return back()->with('success', 'Notifikasi berhasil dikirim ke ' . $customer->username);
    }

    public function applyThrottle(Request $request, Customer $customer)
    {
        $recharge = $customer->recharges()
            ->where('status', 'on')
            ->first();

        if (!$recharge) {
            return back()->with('error', 'Tidak ada paket aktif');
        }

        $result = $this->billingCycle->applyThrottle($recharge);

        if ($result) {
            return back()->with('success', 'Throttle berhasil diterapkan ke ' . $customer->username);
        }

        return back()->with('error', 'Gagal menerapkan throttle');
    }

    public function restoreSpeed(Request $request, Customer $customer)
    {
        $recharge = $customer->recharges()
            ->where('status', 'on')
            ->first();

        if (!$recharge) {
            return back()->with('error', 'Tidak ada paket aktif');
        }

        $result = $this->billingCycle->restoreSpeed($recharge);

        if ($result) {
            return back()->with('success', 'Speed berhasil direstore untuk ' . $customer->username);
        }

        return back()->with('error', 'Gagal restore speed');
    }
}
