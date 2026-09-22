<?php

namespace App\Providers\Filament;

use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()

            // Palette de couleurs Azohub
            ->colors([
                'primary'  => Color::hex('#1e3a8a'), // bleu-900 : couleur "Azo"
                'warning'  => Color::hex('#eab308'), // jaune : couleur "hub"
                'success'  => Color::hex('#059669'), // emerald
                'danger'   => Color::hex('#dc2626'), // red
                'info'     => Color::hex('#0284c7'), // sky
                'gray'     => Color::Slate,
            ])

            // Police Figtree (identique à l'app principale)
            ->font('Figtree', 'https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap')

            // Brand
            ->brandName('Azohub')
            ->brandLogo(fn () => view('components.logo-lockup', ['class' => 'h-8 w-auto']))
            ->brandLogoHeight('2rem')
            ->favicon(asset('favicon.svg'))

            // config/livewire.php désactive l'auto-injection du script Livewire (inject_assets=false),
            // nécessaire côté site public pour éviter deux instances Alpine concurrentes (le layout
            // principal démarre Livewire lui-même depuis le bundle Vite — voir app.blade.php). Mais ce
            // réglage est global : sans ce hook, les pages Filament ne reçoivent plus jamais le script
            // Livewire (leur layout, à nous inconnu, ne l'injecte pas manuellement), donc aucun wire:*
            // n'y fonctionne — symptôme observé : les indicateurs de chargement restent bloqués visibles
            // en permanence sur la page de connexion, avant même toute soumission.
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => Blade::render('@livewireStyles @livewireScripts'),
            )

            // Thème : clair par défaut, dark mode disponible
            ->darkMode(true)
            ->defaultThemeMode(ThemeMode::Light)

            // Sidebar
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('16rem')

            // Découverte automatique
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])

            // Groupes de navigation avec icônes
            ->navigationGroups([
                NavigationGroup::make('Utilisateurs')
                    ->icon('heroicon-o-users'),
                NavigationGroup::make('Services')
                    ->icon('heroicon-o-briefcase'),
                NavigationGroup::make('Commandes')
                    ->icon('heroicon-o-shopping-bag'),
                NavigationGroup::make('Paiements')
                    ->icon('heroicon-o-banknotes'),
                NavigationGroup::make('Monétisation')
                    ->icon('heroicon-o-megaphone'),
                NavigationGroup::make('Modération')
                    ->icon('heroicon-o-shield-exclamation'),
                NavigationGroup::make('Paramètres')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsible(true),
            ])

            // Middleware
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                \App\Http\Middleware\EnsureAccountIsActive::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
