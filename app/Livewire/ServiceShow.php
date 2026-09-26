<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Models\Favorite;
use App\Models\Service;
use App\Models\ServiceView;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class ServiceShow extends Component
{
    public Service $service;
    public $selectedPackage = 'basic';
    public bool $isFavorited = false;
    public int $favoritesCount = 0;

    public function mount(Service $service)
    {
        $this->service = $service;
        
        // Rediriger si le service n'est plus disponible (sauf pour son propriétaire ou un admin,
        // qui peuvent toujours le prévisualiser).
        $canPreview = auth()->check() && (auth()->id() === $service->user_id || auth()->user()->role === 'admin');

        if (!$canPreview && !$service->isOrderable()) {
            return redirect()->route('services.index')
                ->with('error', 'Ce service n\'est plus disponible.');
        }

        // Une vue par visiteur (ou par IP pour un invité) et par heure — même principe que les
        // vues de profil : sinon un rechargement de page fausse la statistique du prestataire.
        if (auth()->id() !== $service->user_id) {
            $viewerKey = auth()->id() ?? 'guest:' . request()->ip();
            $throttleKey = "service-view:{$service->id}:{$viewerKey}";

            if (!Cache::has($throttleKey)) {
                Cache::put($throttleKey, true, now()->addHour());

                ServiceView::create([
                    'service_id' => $service->id,
                    'viewer_id' => auth()->id(),
                    'viewed_at' => now(),
                ]);
            }
        }

        $this->isFavorited = $service->isFavoritedBy(auth()->user());
        $this->favoritesCount = $service->favorites()->count();
    }

    public function selectPackage($package)
    {
        $this->selectedPackage = $package;
    }

    // Seuls les clients peuvent mettre un service en favori (un prestataire ne "commande" pas).
    public function toggleFavorite()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!Auth::user()->isClient()) {
            session()->flash('error', 'Seuls les clients peuvent ajouter un service à leurs favoris.');
            return;
        }

        $favorite = Favorite::where('user_id', Auth::id())->where('service_id', $this->service->id)->first();

        if ($favorite) {
            $favorite->delete();
            $this->isFavorited = false;
            $this->favoritesCount = max(0, $this->favoritesCount - 1);
        } else {
            Favorite::create(['user_id' => Auth::id(), 'service_id' => $this->service->id]);
            $this->isFavorited = true;
            $this->favoritesCount++;
        }
    }

    public function orderService()
    {
        // Vérifier si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('info', 'Veuillez vous connecter pour commander ce service.');
        }

        // Vérifier que l'utilisateur n'est pas le prestataire du service
        if (auth()->id() === $this->service->user_id) {
            session()->flash('error', 'Vous ne pouvez pas commander votre propre service.');
            return;
        }

        // Rediriger vers la page de commande (à créer)
        return redirect()->route('orders.create', [
            'service' => $this->service->id,
            'package' => $this->selectedPackage
        ]);
    }

    public function contactPrestataire()
    {
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('info', 'Veuillez vous connecter pour contacter ce prestataire.');
        }

        if (!auth()->user()->isClient()) {
            session()->flash('error', 'Seuls les clients peuvent contacter directement un prestataire.');
            return;
        }

        $conversation = Conversation::firstOrCreate(
            ['client_id' => auth()->id(), 'prestataire_id' => $this->service->user_id],
            ['service_id' => $this->service->id]
        );

        if (!$conversation->service_id) {
            $conversation->update(['service_id' => $this->service->id]);
        }

        return redirect()->route('conversations.show', $conversation);
    }

    public function render()
    {
        // Charger les relations nécessaires
        $this->service->load([
            'prestataire',
            'category',
            'portfolios',
            'reviews' => function($query) {
                $query->visible()->latest()->take(10);
            },
            'reviews.reviewer'
        ]);

        // Services similaires (même catégorie, même ville)
        $similarServices = Service::active()
            ->where('id', '!=', $this->service->id)
            ->where('category_id', $this->service->category_id)
            ->whereHas('prestataire', function($query) {
                $query->where('city', $this->service->prestataire->city);
            })
            ->with(['prestataire', 'category'])
            ->orderBy('rating', 'desc')
            ->take(4)
            ->get();

        return view('livewire.service-show', [
            'similarServices' => $similarServices,
        ])->layout('components.layouts.app', $this->seoData());
    }

    // Titre/description/image et données structurées Schema.org (Service + avis) : aide Google à
    // comprendre la page (prix, note, avis) et peut faire apparaître des étoiles dans les résultats.
    private function seoData(): array
    {
        $service = $this->service;
        $description = \Illuminate\Support\Str::limit(strip_tags($service->description), 155);

        // Les services de démonstration stockent parfois une URL externe (Unsplash) telle
        // quelle dans cover_image plutôt qu'un chemin local — les vrais envois de prestataires
        // sont toujours locaux, mais autant gérer les deux plutôt que produire une URL cassée.
        $ogImage = $service->cover_image
            ? (\Illuminate\Support\Str::startsWith($service->cover_image, 'http') ? $service->cover_image : \Illuminate\Support\Facades\Storage::url($service->cover_image))
            : null;

        // @type "Service" n'est pas dans la liste des types que Google reconnaît pour le rich
        // result "extrait d'avis" (AggregateRating) — Search Console a signalé une erreur
        // "Type d'objet non valide" dès qu'un service recevait son premier avis. "Product" en
        // fait partie et correspond bien à ce qui est vendu ici (un service à prix fixe, avec
        // offre et avis) : c'est le type que la documentation Google recommande dans ce cas.
        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $service->title,
            'description' => $description,
            'brand' => [
                '@type' => 'Brand',
                'name' => $service->prestataire->name,
            ],
            'offers' => [
                '@type' => 'Offer',
                'price' => (string) $service->price,
                'priceCurrency' => 'XOF',
                'availability' => $service->isOrderable() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            ],
        ];

        if ($ogImage) {
            $jsonLd['image'] = $ogImage;
        }

        if ($service->total_reviews > 0) {
            $jsonLd['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => (string) $service->rating,
                'reviewCount' => (string) $service->total_reviews,
            ];
        }

        return [
            'title' => $service->title . ' — ' . $service->category->name,
            'description' => $description,
            'ogImage' => $ogImage,
            'ogType' => 'product',
            // Un service qu'on ne voit que parce qu'on en est le propriétaire (en modération,
            // refusé, désactivé) ne doit jamais s'indexer — même s'il n'est pas bloqué par
            // robots.txt, un visiteur ne devrait jamais tomber dessus depuis une recherche.
            'noindex' => !$service->isOrderable(),
            'jsonLd' => $jsonLd,
        ];
    }
}