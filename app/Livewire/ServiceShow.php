<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Models\Service;
use Livewire\Component;

class ServiceShow extends Component
{
    public Service $service;
    public $selectedPackage = 'basic';

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
    }

    public function selectPackage($package)
    {
        $this->selectedPackage = $package;
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
        ])->layout('components.layouts.app');
    }
}