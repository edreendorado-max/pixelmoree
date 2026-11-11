<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        return view('home', [
            'notifications' => $user->notifications()->latest()->paginate(20),
            'unreadNotificationCount' => $user->unreadNotifications()->count(),
        ]);
    }

    public function markAsRead(Request $request, string $notification): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $user->notifications()->whereKey($notification)->firstOrFail()->markAsRead();

        return redirect()->route('user.notifications')->with('status', 'Notification marked as read.');
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $user->unreadNotifications()->update(['read_at' => now()]);

        return redirect()->route('user.notifications')->with('status', 'All notifications marked as read.');
    }
}
