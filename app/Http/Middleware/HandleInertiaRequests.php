<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => fn () => $this->resolveUser($request),
                'guard' => fn () => $this->resolveGuard($request),
            ],
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'error' => fn () => $request->session()->get('error'),
                'success' => fn () => $request->session()->get('success'),
            ],
        ];
    }

    private function resolveUser(Request $request): ?array
    {
        if (Auth::guard('admin')->check()) {
            $user = Auth::guard('admin')->user();
            return [
                'id' => $user->id,
                'username' => $user->username,
                'fullname' => $user->fullname ?? $user->username,
                'email' => $user->email ?? '',
                'phone' => $user->phone ?? '',
                'status' => $user->status ?? 'Active',
                'user_type' => $user->user_type ?? 'Admin',
            ];
        }

        if (Auth::guard('customer')->check()) {
            $customer = Auth::guard('customer')->user();
            return [
                'id' => $customer->id,
                'username' => $customer->username,
                'fullname' => $customer->name ?? $customer->username,
                'email' => $customer->email ?? '',
                'phone' => $customer->phone ?? '',
                'status' => $customer->status ?? 'Active',
                'balance' => $customer->balance ?? 0,
            ];
        }

        return null;
    }

    private function resolveGuard(Request $request): ?string
    {
        if (Auth::guard('admin')->check()) {
            return 'admin';
        }

        if (Auth::guard('customer')->check()) {
            return 'customer';
        }

        return null;
    }
}
