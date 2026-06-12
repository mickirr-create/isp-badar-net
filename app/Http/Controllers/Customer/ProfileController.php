<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function show()
    {
        $customer = Auth::guard('customer')->user()->load('recharges.plan', 'fields', 'transactions');

        return Inertia::render('Customer/Profile', [
            'customer' => $customer,
        ]);
    }

    public function update(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $customer->update($validated);

        return back()->with('success', 'Profile updated successfully');
    }

    public function changePassword(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $validated = $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if (!password_verify($validated['current_password'], $customer->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        $customer->update(['password' => bcrypt($validated['password'])]);

        return back()->with('success', 'Password changed successfully');
    }
}
