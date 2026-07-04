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
        $query = AuditLog::with('user')
            ->filter($request->only('search', 'user_id', 'event'));

        $logs = $query->latest()->paginate(15)->withQueryString();
        $users = User::get(['id', 'name']);

        return Inertia::render('audit-logs/Index', [
            'logs' => $logs,
            'users' => $users,
            'filters' => $request->only(['search', 'user_id', 'event']),
        ]);
    }
}
