<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Blade;
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
    }
}
