<?php

namespace App\Livewire;

use App\Models\Service;
use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;

class ServicesIndex extends Component
{
    use WithPagination;

    // Filtres
    public $search = '';
    public $category = '';
    public $city = '';
    public $minPrice = '';
    public $maxPrice = '';
    public $minRating = '';
    public $sortBy = 'recent'; // recent, popular, price_low, price_high, rating

    protected $queryString = [
        'search' => ['except' => ''],
        'category' => ['except' => ''],
        'city' => ['except' => ''],
        'sortBy' => ['except' => 'recent'],
    ];

    public function mount()
    {
        // Récupère les paramètres de recherche depuis la page d'accueil
        $this->search = request('search', '');
        $this->category = request('category', '');
        $this->city = request('city', '');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategory()
    {
        $this->resetPage();
    }

    public function updatingCity()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->category = '';
        $this->city = '';
        $this->minPrice = '';
        $this->maxPrice = '';
        $this->minRating = '';
        $this->sortBy = 'recent';
        $this->resetPage();
    }

    public function render()
    {
        $query = Service::active()
            ->with(['prestataire', 'category'])
            ->withCount('orders')
            // Avantage "apparition prioritaire" des plans payants : les prestataires
            // Premium puis Pro remontent avant le tri choisi par l'utilisateur.
            ->leftJoin('users', 'users.id', '=', 'services.user_id')
            ->leftJoin('subscriptions', function ($join) {
                $join->on('subscriptions.user_id', '=', 'users.id')
                    ->where('subscriptions.status', 'active')
                    ->where('subscriptions.starts_at', '<=', now())
                    ->where('subscriptions.ends_at', '>=', now());
            })
            ->leftJoin('subscription_plans', 'subscription_plans.id', '=', 'subscriptions.subscription_plan_id')
            ->addSelect('services.*')
            ->addSelect('subscription_plans.slug as prestataire_plan_slug')
            ->selectRaw("CASE
                WHEN services.is_featured = 1 AND subscription_plans.slug = 'premium' THEN 3
                WHEN subscription_plans.slug = 'premium' THEN 2
                WHEN subscription_plans.slug = 'pro' THEN 1
                ELSE 0
            END as plan_priority");

        // Recherche par mot-clé
        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhere('tags', 'like', '%' . $this->search . '%');
            });
        }

        // Filtre par catégorie
        if (!empty($this->category)) {
            $query->whereHas('category', function($q) {
                $q->where('slug', $this->category);
            });
        }

        // Filtre par ville (ville du prestataire)
        if (!empty($this->city)) {
            $query->whereHas('prestataire', function($q) {
                $q->where('city', $this->city);
            });
        }

        // Filtre par prix
        if (!empty($this->minPrice)) {
            $query->where('services.price', '>=', $this->minPrice);
        }
        if (!empty($this->maxPrice)) {
            $query->where('services.price', '<=', $this->maxPrice);
        }

        // Filtre par note
        if (!empty($this->minRating)) {
            $query->where('services.rating', '>=', $this->minRating);
        }

        // Tri : priorité au plan payant d'abord, puis le critère choisi par l'utilisateur
        $query->orderBy('plan_priority', 'desc');

        switch ($this->sortBy) {
            case 'popular':
                $query->orderBy('orders_count', 'desc');
                break;
            case 'price_low':
                $query->orderBy('services.price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('services.price', 'desc');
                break;
            case 'rating':
                $query->orderBy('services.rating', 'desc');
                break;
            case 'recent':
            default:
                $query->orderBy('services.created_at', 'desc');
                break;
        }

        $services = $query->paginate(12);
        $categories = Category::active()->get();
        
        // Récupère toutes les 77 communes du Bénin
        $communes = config('communes', []);
        $cities = [];
        foreach ($communes as $dept => $villes) {
            foreach ($villes as $ville) {
                $cities[] = $ville;
            }
        }
        sort($cities);

        return view('livewire.services-index', [
            'services' => $services,
            'categories' => $categories,
            'cities' => $cities,
        ])->layout('components.layouts.app');
    }
}