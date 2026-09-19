<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationBell extends Component
{
    public function markAsRead(string $notificationId)
    {
        $notification = Auth::user()->notifications()->where('id', $notificationId)->first();

        if ($notification && is_null($notification->read_at)) {
            $notification->markAsRead();
        }
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
    }

    public function render()
    {
        // Uniquement le non-lu : une fois marquée lue, la notification disparaît de la cloche.
        $unreadCount = Auth::user()->unreadNotifications()->count();
        $notifications = Auth::user()->unreadNotifications()->latest()->take(10)->get();

        return view('livewire.notification-bell', compact('notifications', 'unreadCount'));
    }
}
