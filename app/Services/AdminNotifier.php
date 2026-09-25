<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\AdminActionRequired;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Support\Facades\Notification;

// Point d'entrée unique pour prévenir les administrateurs qu'un élément attend leur action
// (litige, signalement, vérification d'identité, demande de retrait, service à modérer...).
// Envoie à la fois un email (App\Notifications\AdminActionRequired) et une notification dans
// la cloche du panneau admin (Filament\Notifications\Notification::sendToDatabase(), le seul
// format que cette cloche sait lire dans la table `notifications`).
class AdminNotifier
{
    public static function actionRequired(string $title, string $message, string $url): void
    {
        $admins = User::where('role', 'admin')->get();

        if ($admins->isEmpty()) {
            return;
        }

        Notification::send($admins, new AdminActionRequired($title, $message, $url));

        FilamentNotification::make()
            ->title($title)
            ->body($message)
            ->icon('heroicon-o-bell-alert')
            ->actions([
                \Filament\Actions\Action::make('view')
                    ->label('Voir')
                    ->url($url)
                    ->button(),
            ])
            ->sendToDatabase($admins);
    }
}
