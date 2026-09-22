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

    // Mots de la recherche (6 au maximum, doublons ignorés).
    private function searchWords(): array
    {
        return collect(preg_split('/\s+/u', trim((string) $this->search), -1, PREG_SPLIT_NO_EMPTY))
            ->unique(fn ($w) => mb_strtolower($w))
            ->take(6)
            ->values()
            ->all();
    }

    public function render()
    {
        // Ces filtres viennent du navigateur : on borne/assainit avant de les injecter dans la requête.
        $this->search = mb_substr((string) $this->search, 0, 100);
        $this->city = mb_substr((string) $this->city, 0, 100);
        $this->category = mb_substr((string) $this->category, 0, 100);
        $this->minPrice = is_numeric($this->minPrice) ? $this->minPrice : '';
        $this->maxPrice = is_numeric($this->maxPrice) ? $this->maxPrice : '';
        $this->minRating = is_numeric($this->minRating) ? min(5, max(0, (float) $this->minRating)) : '';
        if (!in_array($this->sortBy, ['recent', 'popular', 'price_low', 'price_high', 'rating'], true)) {
            $this->sortBy = 'recent';
        }

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

        // Recherche par mots-clés : chaque mot saisi doit se retrouver (dans le titre, la description,
        // les tags, le nom de la catégorie ou celui du prestataire) ; l'ordre des mots n'a pas d'importance.
        foreach ($this->searchWords() as $word) {
            $like = '%' . addcslashes($word, '%_\\') . '%';

            $query->where(function ($q) use ($like) {
                $q->where('services.title', 'like', $like)
                  ->orWhere('services.description', 'like', $like)
                  ->orWhere('services.tags', 'like', $like)
                  ->orWhereHas('category', fn ($c) => $c->where('name', 'like', $like))
                  ->orWhereHas('prestataire', fn ($p) => $p->where('name', 'like', $like));
            });
        }

        // Filtre par catégorie
        if (!empty($this->category)) {
            $query->whereHas('category', function($q) {
                $q->where('slug', $this->category);
            });
        }

        // Filtre par ville : le service couvre cette commune (zones d'intervention), ou tout le Bénin.
        // Un ancien service sans zone définie se limite à sa ville (celle du service, sinon du prestataire).
        if (!empty($this->city)) {
            $query->where(function ($q) {
                $q->where('services.serves_nationwide', true)
                  ->orWhereJsonContains('services.service_areas', $this->city)
                  ->orWhere(function ($legacy) {
                      $legacy->whereNull('services.service_areas')
                          ->where('services.serves_nationwide', false)
                          ->where(function ($c) {
                              $c->where('services.city', $this->city)
                                ->orWhereHas('prestataire', fn ($p) => $p->where('city', $this->city));
                          });
                  });
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

        // Tri par défaut avec une ville : les services locaux passent avant ceux « partout au Bénin »
        // (à niveau d'abonnement égal). Un tri explicite (prix, note…) reste respecté tel quel.
        if (!empty($this->city) && $this->sortBy === 'recent') {
            $query->orderBy('services.serves_nationwide', 'asc');
        }

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

        $seoTitle = 'Tous les services';
        if (!empty($this->category)) {
            $seoTitle = ($categories->firstWhere('slug', $this->category)?->name ?? 'Services') . ' au Bénin';
        } elseif (!empty($this->search)) {
            $seoTitle = 'Résultats pour « ' . $this->search . ' »';
        }

        return view('livewire.services-index', [
            'services' => $services,
            'categories' => $categories,
            'cities' => $cities,
        ])->layout('components.layouts.app', [
            'title' => $seoTitle,
            'description' => "Parcourez les services de prestataires qualifiés partout au Bénin : filtrez par catégorie, ville et prix, comparez les avis, commandez en toute sécurité.",
            // Une recherche ou un tri ne devrait jamais s'indexer séparément de la liste de base :
            // sinon Google voit des centaines de pages quasi identiques (une par combinaison de
            // filtres), ce qui dilue son intérêt pour la vraie page de liste.
            'canonical' => (!empty($this->search) || !empty($this->minPrice) || !empty($this->maxPrice) || !empty($this->minRating))
                ? route('services.index', array_filter(['category' => $this->category, 'city' => $this->city]))
                : null,
        ]);
    }
}