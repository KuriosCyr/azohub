<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Service;
use App\Models\User;
use Livewire\Component;

class HomePage extends Component
{
    public $search = '';
    public $city = '';

    public function render()
    {
        return view('livewire.home-page', [
            'categories' => Category::active()->take(10)->get(),
            'popularServices' => Service::active()
                ->with(['prestataire', 'category'])
                ->withCount('orders')  // Compte dynamiquement les commandes
                ->orderBy('orders_count', 'desc')
                ->orderBy('rating', 'desc')
                ->take(8)
                ->get(),
            'stats' => [
                'services' => Service::active()->count(),
                'prestataires' => User::where('role', 'prestataire')->count(),
                'orders' => \App\Models\Order::where('status', 'completed')->count(),
            ]
        ])->layout('components.layouts.app', [
            'description' => "Trouvez un plombier, un électricien, un designer ou tout autre prestataire qualifié au Bénin. Paiement sécurisé, livraison suivie, avis vérifiés.",
            // Deux entités distinctes (Organization + WebSite) plutôt qu'une seule : c'est ce que
            // la documentation de Google sur les "noms de site" dans les résultats de recherche
            // (celui affiché en gras au-dessus du lien, ex. "Azohub" plutôt que "azohub.bj")
            // demande explicitement pour influencer ce nom.
            'jsonLd' => [
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'Organization',
                    'name' => 'Azohub',
                    'url' => url('/'),
                    // Google ne lit pas le SVG comme logo de structured data (formats acceptés :
                    // JPG/PNG/WebP) : le favicon.svg était donc silencieusement ignoré. On réutilise
                    // le PNG déjà utilisé comme logo dans les emails, seul raster déjà en place.
                    'logo' => asset('images/email-logo.png'),
                    'areaServed' => 'Bénin',
                ],
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'WebSite',
                    'name' => 'Azohub',
                    'url' => url('/'),
                ],
            ],
        ]);
    }

    public function searchServices()
    {
        $params = [];
        
        if (!empty($this->search)) {
            $params['search'] = $this->search;
        }
        
        if (!empty($this->city)) {
            $params['city'] = $this->city;
        }
        
        if (empty($params)) {
            return redirect()->route('services.index');
        }

        return redirect()->route('services.index', $params);
    }
}