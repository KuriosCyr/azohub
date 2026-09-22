<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    /**
     * robots.txt — généré dynamiquement (plutôt qu'un fichier statique dans public/) pour que la
     * ligne Sitemap utilise toujours la vraie URL de l'app (config/app.php APP_URL), sans jamais
     * pointer vers l'adresse IP une fois le domaine branché.
     */
    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /login',
            'Disallow: /register',
            'Disallow: /profile',
            'Disallow: /forgot-password',
            'Disallow: /reset-password',
            'Disallow: /verify-email',
            'Disallow: /dashboard',
            'Disallow: /prestataire/dashboard',
            'Disallow: /prestataire/wallet',
            'Disallow: /prestataire/abonnement',
            'Disallow: /prestataire/statistiques',
            'Disallow: /prestataire/commandes',
            'Disallow: /prestataire/avis',
            'Disallow: /prestataire/services',
            'Disallow: /client/dashboard',
            'Disallow: /clients/',
            'Disallow: /messages',
            'Disallow: /orders',
            'Disallow: /demandes/nouvelle',
            'Disallow: /offres-personnalisees',
            'Disallow: /reviews',
            'Disallow: /identity-verification',
            'Disallow: /payments',
            '',
            'Sitemap: ' . url('/sitemap.xml'),
        ];

        return response(implode("\n", $lines), 200)->header('Content-Type', 'text/plain');
    }

    /**
     * sitemap.xml — uniquement les pages publiques et réellement indexables (services publiés et
     * commandables, profils prestataires actifs, pages statiques). Régénéré à chaque requête à
     * partir des données réelles : jamais besoin de le reconstruire à la main après un déploiement.
     */
    public function sitemap(): Response
    {
        $urls = collect();

        foreach (['home', 'services.index', 'faq', 'contact', 'how-it-works', 'terms', 'privacy'] as $route) {
            $urls->push(['loc' => route($route), 'priority' => $route === 'home' ? '1.0' : '0.6']);
        }

        Category::active()->get()->each(function (Category $category) use ($urls) {
            $urls->push(['loc' => route('services.index', ['category' => $category->slug]), 'priority' => '0.7']);
        });

        Service::active()->get()->each(function (Service $service) use ($urls) {
            $urls->push([
                'loc' => route('services.show', $service->slug),
                'lastmod' => $service->updated_at->toAtomString(),
                'priority' => '0.8',
            ]);
        });

        User::where('role', 'prestataire')->where('is_active', true)->whereNotNull('slug')->get()
            ->each(function (User $prestataire) use ($urls) {
                $urls->push([
                    'loc' => route('prestataire.profile', $prestataire->slug),
                    'lastmod' => $prestataire->updated_at->toAtomString(),
                    'priority' => '0.6',
                ]);
            });

        $xml = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
