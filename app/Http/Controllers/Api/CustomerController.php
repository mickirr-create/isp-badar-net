<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\UserRecharge;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CustomerController extends Controller
{
    public function me(Request $request): JsonResponse
    {
        $customer = Auth::guard('sanctum')->user();

        if (!$customer) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        return response()->json([
            'id' => $customer->id,
            'username' => $customer->username,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'balance' => $customer->balance,
            'status' => $customer->status,
        ]);
    }

    public function balance(Request $request): JsonResponse
    {
        $customer = Auth::guard('sanctum')->user();

        if (!$customer) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        return response()->json([
            'balance' => $customer->balance,
            'username' => $customer->username,
        ]);
    }

    public function activePlan(Request $request): JsonResponse
    {
        $customer = Auth::guard('sanctum')->user();

        if (!$customer) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $recharge = UserRecharge::with('plan')
            ->where('customer_id', $customer->id)
            ->where('status', 'on')
            ->where('expiration', '>=', Carbon::today())
            ->first();

        if (!$recharge) {
            return response()->json(['active_plan' => null]);
        }

        return response()->json([
            'active_plan' => [
                'plan_name' => $recharge->namebp,
                'router' => $recharge->routers,
                'expiration' => $recharge->expiration,
                'time' => $recharge->time,
            ],
        ]);
    }

    public function plans(Request $request): JsonResponse
    {
        $plans = Plan::with('bandwidth')
            ->where('enabled', 1)
            ->orderBy('name_plan')
            ->get()
            ->map(fn($plan) => [
                'id' => $plan->id,
                'name' => $plan->name_plan,
                'price' => $plan->price,
                'validity' => $plan->validity,
                'validity_unit' => $plan->validity_unit,
                'type' => $plan->type,
                'description' => $plan->description,
                'bandwidth' => $plan->bandwidth?->name_bw,
            ]);

        return response()->json($plans);
    }

    public function transactions(Request $request): JsonResponse
    {
        $customer = Auth::guard('sanctum')->user();

        if (!$customer) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $transactions = Transaction::where('user_id', $customer->id)
            ->orderBy('id', 'desc')
            ->limit(50)
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'invoice' => $t->invoice,
                'plan_name' => $t->plan_name,
                'price' => $t->price,
                'method' => $t->method,
                'recharged_on' => $t->recharged_on,
                'type' => $t->type,
            ]);

        return response()->json($transactions);
    }
}
