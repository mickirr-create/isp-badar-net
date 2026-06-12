<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppConfig;
use App\Models\PaymentGateway;
use App\Services\Payment\PaymentGatewayFactory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentGatewayController extends Controller
{
    public function index()
    {
        $gateways = PaymentGatewayFactory::available();

        $configs = [];
        foreach ($gateways as $name) {
            $configs[$name] = [
                'name' => $name,
                'enabled' => AppConfig::getSetting("{$name}_enabled", 'false') === 'true',
                'settings' => $this->getGatewaySettings($name),
            ];
        }

        return Inertia::render('Admin/PaymentGateways/Index', [
            'gateways' => $configs,
        ]);
    }

    public function update(Request $request, string $gateway)
    {
        $validated = $request->validate([
            'enabled' => 'required|boolean',
            'settings' => 'nullable|array',
        ]);

        AppConfig::setSetting("{$gateway}_enabled", $validated['enabled'] ? 'true' : 'false');

        if (isset($validated['settings'])) {
            foreach ($validated['settings'] as $key => $value) {
                AppConfig::setSetting($key, $value);
            }
        }

        return redirect()->route('admin.payment-gateways.index')->with('success', "{$gateway} gateway updated");
    }

    public function audit(Request $request)
    {
        $query = PaymentGateway::with(['plan', 'router']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('gateway', 'like', "%{$search}%");
            });
        }

        if ($request->filled('gateway')) {
            $query->where('gateway', $request->gateway);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $audits = $query->orderBy('id', 'desc')->paginate(25)->withQueryString();

        return Inertia::render('Admin/PaymentGateways/Audit', [
            'audits' => $audits,
            'filters' => $request->only(['search', 'gateway', 'status']),
        ]);
    }

    public function auditView(PaymentGateway $audit)
    {
        $audit->load(['plan', 'router']);

        return Inertia::render('Admin/PaymentGateways/AuditView', [
            'audit' => $audit,
        ]);
    }

    private function getGatewaySettings(string $gateway): array
    {
        return match ($gateway) {
            'Midtrans' => [
                'midtrans_server_key' => AppConfig::getSetting('midtrans_server_key', ''),
                'midtrans_client_key' => AppConfig::getSetting('midtrans_client_key', ''),
                'midtrans_production' => AppConfig::getSetting('midtrans_production', 'false'),
            ],
            'Xendit' => [
                'xendit_secret_key' => AppConfig::getSetting('xendit_secret_key', ''),
                'xendit_api_key' => AppConfig::getSetting('xendit_api_key', ''),
            ],
            'Tripay' => [
                'tripay_api_key' => AppConfig::getSetting('tripay_api_key', ''),
                'tripay_private_key' => AppConfig::getSetting('tripay_private_key', ''),
                'tripay_merchant_code' => AppConfig::getSetting('tripay_merchant_code', ''),
            ],
            default => [],
        };
    }
}
