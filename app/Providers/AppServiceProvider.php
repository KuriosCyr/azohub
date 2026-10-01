<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        // Layout unique de l'app, utilisé partout via <x-app-layout>.
        Blade::component('components.layouts.app', 'app-layout');

        // Utilisé par Password::defaults() partout où un mot de passe est créé ou changé
        // (inscription, réinitialisation, changement depuis le profil) : sans ça, la seule
        // exigence réelle était 8 caractères minimum, sans aucune complexité.
        Password::defaults(fn () => Password::min(8)->mixedCase()->numbers());

        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage)
                ->subject('Confirmez votre adresse email - Azohub')
                ->greeting('Bonjour ' . $notifiable->name . ',')
                ->line('Merci de votre inscription sur Azohub ! Cliquez sur le bouton ci-dessous pour confirmer votre adresse email.')
                ->action('Confirmer mon email', $url)
                ->line('Si vous n\'avez pas créé de compte, vous pouvez ignorer cet email.');
        });

        // Centralisé plutôt que répété dans chacune des ~33 classes de notification (audit
        // externe — 8e audit) : depuis que Order::client()/prestataire() utilisent withTrashed()
        // (7e audit, pour que l'argent reste crédité même si le compte a été supprimé), les
        // notifications adressées à ce même compte partent vers son e-mail anonymisé
        // (...@azohub.invalid) au lieu d'échouer sur null — chaque envoi échoue ensuite dans la
        // file et remplit failed_jobs sans but utile. Annule l'envoi (tous canaux) dès que le
        // destinataire est un compte supprimé.
        Event::listen(NotificationSending::class, function (NotificationSending $event) {
            if ($event->notifiable instanceof User && $event->notifiable->trashed()) {
                return false;
            }
        });
    }
}
