<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $query = Transaction::where('user_id', $customer->id);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $transactions = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        return Inertia::render('Customer/Transactions', [
            'transactions' => $transactions,
            'filters' => $request->only(['type']),
        ]);
    }
}
