<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Models\ProfileView;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;

class PrestataireProfile extends Component
{
    use WithPagination;

    public User $prestataire;
    public $activeTab = 'services'; // services, reviews, about

    public function mount($username)
    {
        $this->prestataire = User::where('role', 'prestataire')
            ->where('slug', $username)
            ->firstOrFail();

        // Vérifier que le compte est actif
        if (!$this->prestataire->is_active) {
            abort(404, 'Ce profil n\'est pas disponible.');
        }

        // Statistiques (avantage des plans payants) : on ne compte pas les visites du
        // prestataire sur son propre profil, et une seule vue par visiteur et par heure —
        // sinon un simple rechargement de page gonflerait le compteur à l'infini (et le
        // rendrait trivialement manipulable, à la hausse comme pour nuire à un concurrent).
        // session()->getId() plutôt que request()->ip() pour un invité (audit externe — 7e
        // audit) : au Bénin, de nombreux abonnés mobiles partagent la même IP publique (NAT
        // opérateur) — une clé par IP sous-comptait fortement les vues invitées.
        if (Auth::id() !== $this->prestataire->id) {
            $viewerKey = Auth::id() ?? 'guest:' . session()->getId();
            $throttleKey = "profile-view:{$this->prestataire->id}:{$viewerKey}";

            // Un invité SANS cookie de session déjà existant n'est pas compté (audit externe —
            // 8e audit) : sans ça, un robot sans cookies obtenait une NOUVELLE session à chaque
            // requête, gonflant le compteur à l'infini.
            $isReturningVisitor = Auth::check() || request()->hasCookie(config('session.cookie'));

            if ($isReturningVisitor && !Cache::has($throttleKey)) {
                Cache::put($throttleKey, true, now()->addHour());

                ProfileView::create([
                    'prestataire_id' => $this->prestataire->id,
                    'viewer_id' => Auth::id(),
                    'viewed_at' => now(),
                ]);
            }
        }
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
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

        $conversation = Conversation::firstOrCreate([
            'client_id' => auth()->id(),
            'prestataire_id' => $this->prestataire->id,
        ]);

        return redirect()->route('conversations.show', $conversation);
    }

    public function render()
    {
        $data = [];

        // Charger les données selon l'onglet actif
        if ($this->activeTab === 'services') {
            $data['services'] = $this->prestataire->services()
                ->active()
                ->with('category')
                ->withCount('orders')
                ->orderBy('orders_count', 'desc')
                ->paginate(12);
        } elseif ($this->activeTab === 'reviews') {
            $data['reviews'] = $this->prestataire->receivedReviews()
                ->visible()
                ->with(['reviewer', 'order.service'])
                ->latest()
                ->paginate(10);
        }

        // Statistiques
        $data['stats'] = [
            'services_count' => $this->prestataire->services()->active()->count(),
            'total_orders' => $this->prestataire->completed_orders,
            'rating' => $this->prestataire->rating,
            'total_reviews' => $this->prestataire->receivedReviews()->visible()->count(),
            'response_time' => $this->prestataire->responseTimeLabel(),
            'completion_rate' => $this->prestataire->completionRate(),
            // Null tant que l'historique est trop court pour être honnête (voir
            // User::PUNCTUALITY_MIN_SAMPLE) : jamais affiché publiquement en dessous.
            'on_time_delivery_rate' => $this->prestataire->public_on_time_delivery_rate,
        ];

        return view('livewire.prestataire-profile', $data)->layout('components.layouts.app', [
            'title' => $this->prestataire->name . ($this->prestataire->city ? ' — ' . $this->prestataire->city : ''),
            'description' => $this->prestataire->bio
                ? \Illuminate\Support\Str::limit(strip_tags($this->prestataire->bio), 155)
                : "{$this->prestataire->name}, prestataire sur Azohub" . ($this->prestataire->city ? " à {$this->prestataire->city}" : '') . '. Consultez ses services, avis et disponibilités.',
            'ogImage' => $this->prestataire->avatar ? \Illuminate\Support\Facades\Storage::url($this->prestataire->avatar) : null,
            'ogType' => 'profile',
        ]);
    }
}