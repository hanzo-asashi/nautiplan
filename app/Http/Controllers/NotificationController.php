<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function stream(): StreamedResponse
    {
        return response()->stream(function () {
            $userId = auth()->id();

            // Send initial batch of unread notifications
            $unreadNotifications = Notification::where(function ($q) use ($userId) {
                $q->where('user_id', $userId)->orWhereNull('user_id');
            })
                ->whereNull('read_at')
                ->latest()
                ->limit(10)
                ->get();

            echo "event: initial\n";
            echo 'data: '.json_encode($unreadNotifications)."\n\n";
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();

            // Track last execution time
            $lastCheck = now();

            while (true) {
                if (connection_aborted()) {
                    break;
                }

                // Poll for notifications created since the last check
                $newNotifications = Notification::where(function ($q) use ($userId) {
                    $q->where('user_id', $userId)->orWhereNull('user_id');
                })
                    ->whereNull('read_at')
                    ->where('created_at', '>=', $lastCheck)
                    ->latest()
                    ->get();

                $lastCheck = now();

                if ($newNotifications->isNotEmpty()) {
                    echo "event: new-notification\n";
                    echo 'data: '.json_encode($newNotifications)."\n\n";
                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();
                }

                sleep(2);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function markAsRead(Notification $notification): RedirectResponse
    {
        if ($notification->user_id === auth()->id() || is_null($notification->user_id)) {
            $notification->update(['read_at' => now()]);
        }

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
