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
            ->withCount('orders');

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
            $query->where('price', '>=', $this->minPrice);
        }
        if (!empty($this->maxPrice)) {
            $query->where('price', '<=', $this->maxPrice);
        }

        // Filtre par note
        if (!empty($this->minRating)) {
            $query->where('rating', '>=', $this->minRating);
        }

        // Tri
        switch ($this->sortBy) {
            case 'popular':
                $query->orderBy('orders_count', 'desc');
                break;
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'rating':
                $query->orderBy('rating', 'desc');
                break;
            case 'recent':
            default:
                $query->orderBy('created_at', 'desc');
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
        ])->layout('components.layouts.public');
    }
}