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
            'jsonLd' => [
                '@context' => 'https://schema.org',
                '@type' => 'Organization',
                'name' => 'Azohub',
                'url' => url('/'),
                'logo' => asset('design-exports/azohub-symbol.svg'),
                'areaServed' => 'Bénin',
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