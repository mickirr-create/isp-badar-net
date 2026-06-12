<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showLogin()
    {
        return Inertia::render('Customer/Login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::guard('customer')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $customer = Auth::guard('customer')->user();

            if ($customer->status !== 'Active') {
                Auth::guard('customer')->logout();
                return back()->withErrors([
                    'username' => 'Your account is not active.',
                ]);
            }

            // Update last_login
            $customer->update(['last_login' => now()]);

            return redirect()->intended(route('customer.dashboard'));
        }

        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ])->onlyInput('username');
    }

    public function showRegister()
    {
        return Inertia::render('Customer/Register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:45|unique:tbl_customers,username',
            'password' => 'required|string|min:6|confirmed',
            'fullname' => 'required|string|max:45',
            'email' => 'required|email|max:128|unique:tbl_customers,email',
            'phonenumber' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $customer = Customer::create([
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'fullname' => $validated['fullname'],
            'email' => $validated['email'],
            'phonenumber' => $validated['phonenumber'] ?? '0',
            'address' => $validated['address'] ?? null,
            'status' => 'Active',
            'service_type' => 'Others',
            'balance' => 0,
        ]);

        Auth::guard('customer')->login($customer);

        return redirect()->route('customer.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('customer.login');
    }
}
