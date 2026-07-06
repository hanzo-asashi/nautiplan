<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(): Response
    {
        $userId = auth()->id();
        $notifications = Notification::where(function ($q) use ($userId) {
            $q->where('user_id', $userId)->orWhereNull('user_id');
        })
            ->latest()
            ->paginate(20);

        return Inertia::render('notifications/Index', [
            'notifications' => $notifications,
        ]);
    }

    public function stream(): JsonResponse
    {
        $userId = auth()->id();

        $unreadNotifications = Notification::where(function ($q) use ($userId) {
            $q->where('user_id', $userId)->orWhereNull('user_id');
        })
            ->whereNull('read_at')
            ->latest()
            ->limit(10)
            ->get();

        return response()->json($unreadNotifications);
    }

    public function markAsRead(Notification $notification): RedirectResponse
    {
        if ($notification->user_id !== null && $notification->user_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki wewenang untuk mengakses notifikasi ini.');
        }

        $notification->update(['read_at' => now()]);

        return back()->with('success', 'Notifikasi ditandai sebagai telah dibaca.');
    }

    public function markAllRead(): RedirectResponse
    {
        $userId = auth()->id();
        Notification::where(function ($q) use ($userId) {
            $q->where('user_id', $userId)->orWhereNull('user_id');
        })
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', 'Semua notifikasi ditandai sebagai telah dibaca.');
    }
}
