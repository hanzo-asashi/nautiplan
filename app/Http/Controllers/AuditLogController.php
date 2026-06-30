<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    public function index(Request $request): Response
    {
        $query = AuditLog::with('user');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('event', 'like', "%{$search}%")
                    ->orWhere('auditable_type', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('event')) {
            $query->where('event', $request->input('event'));
        }

        $logs = $query->latest()->paginate(15)->withQueryString();
        $users = User::get(['id', 'name']);

        return Inertia::render('audit-logs/Index', [
            'logs' => $logs,
            'users' => $users,
            'filters' => $request->only(['search', 'user_id', 'event']),
        ]);
    }
}
