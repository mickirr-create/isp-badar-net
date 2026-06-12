<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MessageController extends Controller
{
    public function sendForm()
    {
        $customers = Customer::select('id', 'username', 'name', 'phone')
            ->orderBy('username')
            ->get();

        return Inertia::render('Admin/Messages/Send', [
            'customers' => $customers,
        ]);
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:tbl_customers,id',
            'message' => 'required|string|max:1000',
            'channel' => 'required|string|in:sms,whatsapp,inbox,email',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);

        DB::table('tbl_message_logs')->insert([
            'message_type' => $validated['channel'],
            'recipient' => $customer->phone ?? $customer->email ?? $customer->username,
            'message_content' => $validated['message'],
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return back()->with('success', 'Pesan berhasil dikirim');
    }

    public function bulkForm()
    {
        return Inertia::render('Admin/Messages/Bulk');
    }

    public function bulkSend(Request $request)
    {
        $validated = $request->validate([
            'group' => 'required|string|in:all,active,expired,new',
            'message' => 'required|string|max:1000',
            'channel' => 'required|string|in:sms,whatsapp,inbox,email',
        ]);

        $query = Customer::query();

        if ($validated['group'] === 'active') {
            $query->whereHas('recharges', fn($q) => $q->where('status', 'on'));
        } elseif ($validated['group'] === 'expired') {
            $query->whereHas('recharges', fn($q) => $q->where('status', 'on')->where('expiration', '<', now()));
        } elseif ($validated['group'] === 'new') {
            $query->where('created_at', '>=', now()->subDays(7));
        }

        $customers = $query->get();
        $count = 0;

        foreach ($customers as $customer) {
            $recipient = match ($validated['channel']) {
                'email' => $customer->email,
                default => $customer->phone,
            };

            if (!$recipient) continue;

            DB::table('tbl_message_logs')->insert([
                'message_type' => $validated['channel'],
                'recipient' => $recipient,
                'message_content' => $validated['message'],
                'status' => 'sent',
                'sent_at' => now(),
            ]);
            $count++;
        }

        return back()->with('success', "Pesan massal berhasil dikirim ke {$count} pelanggan");
    }
}
