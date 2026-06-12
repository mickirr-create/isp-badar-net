<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Log;
use App\Models\MessageLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LogsController extends Controller
{
    public function index(Request $request)
    {
        $query = Log::with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('ip', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $logs = $query->orderBy('id', 'desc')->paginate(25)->withQueryString();

        return Inertia::render('Admin/Logs/Index', [
            'logs' => $logs,
            'filters' => $request->only(['search', 'type']),
        ]);
    }

    public function messageLogs(Request $request)
    {
        $query = MessageLog::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('recipient', 'like', "%{$search}%")
                  ->orWhere('message_content', 'like', "%{$search}%");
            });
        }

        if ($request->filled('message_type')) {
            $query->where('message_type', $request->message_type);
        }

        $messageLogs = $query->orderBy('id', 'desc')->paginate(25)->withQueryString();

        return Inertia::render('Admin/Logs/MessageLogs', [
            'messageLogs' => $messageLogs,
            'filters' => $request->only(['search', 'message_type']),
        ]);
    }

    public function destroy(Log $log)
    {
        $log->delete();
        return back()->with('success', 'Log berhasil dihapus');
    }

    public function clearOld(Request $request)
    {
        $days = $request->integer('days', 30);
        Log::where('date', '<', now()->subDays($days))->delete();

        return back()->with('success', "Log lebih dari {$days} hari berhasil dihapus");
    }
}
