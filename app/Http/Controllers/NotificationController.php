<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(12);

        return view('notifications.index', [
            'user' => $request->user(),
            'notifications' => $notifications,
        ]);
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()
            ->notifications()
            ->unread()
            ->update(['read_at' => now()]);

        return back()->with('success', 'Notifications marked as read.');
    }
}
