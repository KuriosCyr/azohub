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
        // Les 10 plus récentes, lues ou non : avant, marquer une notification comme lue la
        // retirait purement et simplement de la liste (elle n'existait nulle part ailleurs pour
        // la revoir) — la ligne restait bien en base, mais plus aucun endroit ne la montrait.
        $unreadCount = Auth::user()->unreadNotifications()->count();
        $notifications = Auth::user()->notifications()->latest()->take(10)->get();

        return view('livewire.notification-bell', compact('notifications', 'unreadCount'));
    }
}
