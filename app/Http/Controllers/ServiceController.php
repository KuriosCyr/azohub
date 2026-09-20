<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    /**
     * Display a listing of the user's services.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Filtres
        $status = $request->input('status', 'all'); // all, active, inactive

        $query = $user->services()->with('category');

        if ($status === 'active') {
            $query->where('is_active', true)->where('status', 'active');
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $services = $query->latest()->paginate(10);

        return view('prestataire.services.index', compact('services', 'status'));
    }

    /**
     * Show the form for creating a new service.
     */
    public function create()
    {
        $this->guardServiceLimit();

        $categories = Category::where('is_active', true)->get();

        return view('prestataire.services.create', compact('categories'));
    }

    /**
     * Store a newly created service in storage.
     */
    public function store(Request $request)
    {
        $this->guardServiceLimit();

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:50|max:5000',
            'what_included' => 'nullable|string|max:3000',
            'price' => 'required|numeric|min:0',
            'price_type' => 'required|in:fixe,a_partir_de',
            'delivery_time' => 'required|integer|min:1|max:365',
            'cover_image' => 'required|image|max:5120', // 5MB
            'portfolio.*' => 'nullable|image|max:5120',
            'tags' => 'nullable|string',
        ], [
            'category_id.required' => 'La catégorie est obligatoire.',
            'title.required' => 'Le titre est obligatoire.',
            'description.required' => 'La description est obligatoire.',
            'description.min' => 'La description doit contenir au moins 50 caractères.',
            'price.required' => 'Le prix est obligatoire.',
            'delivery_time.required' => 'Le délai de livraison est obligatoire.',
            'cover_image.required' => 'L\'image de couverture est obligatoire.',
            'cover_image.image' => 'Le fichier doit être une image.',
            'cover_image.max' => 'L\'image ne doit pas dépasser 5 MB.',
        ]);

        // Upload cover image
        $coverImagePath = $request->file('cover_image')->store('services/covers', 'public');

        // Créer le service
        $service = Auth::user()->services()->create([
            'category_id' => $validated['category_id'],
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']) . '-' . Str::random(6),
            'description' => $validated['description'],
            'what_included' => $validated['what_included'] ?? null,
            'price' => $validated['price'],
            'price_type' => $validated['price_type'],
            'delivery_time' => $validated['delivery_time'],
            'city' => Auth::user()->city,
            'cover_image' => $coverImagePath,
            'tags' => $validated['tags'] ? array_map('trim', explode(',', $validated['tags'])) : null,
            'status' => 'active',
            'is_active' => true,
        ]);

        // Upload des images portfolio (une ligne par fichier dans la table portfolios)
        if ($request->hasFile('portfolio')) {
            foreach ($request->file('portfolio') as $image) {
                $service->portfolios()->create([
                    'file_path' => $image->store('services/portfolio', 'public'),
                    'file_type' => 'image',
                ]);
            }
        }

        return redirect()
            ->route('prestataire.services.index')
            ->with('success', 'Service créé avec succès !');
    }

    /**
     * Display the specified service.
     */
    public function show(Service $service)
    {
        // Vérifier que le service appartient au prestataire
        if ($service->user_id !== Auth::id()) {
            abort(403);
        }

        $service->load(['category', 'orders.client', 'reviews.reviewer']);

        return view('prestataire.services.show', compact('service'));
    }

    /**
     * Show the form for editing the specified service.
     */
    public function edit(Service $service)
    {
        // Vérifier que le service appartient au prestataire
        if ($service->user_id !== Auth::id()) {
            abort(403);
        }

        $categories = Category::where('is_active', true)->get();

        return view('prestataire.services.edit', compact('service', 'categories'));
    }

    /**
     * Update the specified service in storage.
     */
    public function update(Request $request, Service $service)
    {
        // Vérifier que le service appartient au prestataire
        if ($service->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:50|max:5000',
            'what_included' => 'nullable|string|max:3000',
            'price' => 'required|numeric|min:0',
            'price_type' => 'required|in:fixe,a_partir_de',
            'delivery_time' => 'required|integer|min:1|max:365',
            'cover_image' => 'nullable|image|max:5120',
            'portfolio.*' => 'nullable|image|max:5120',
            'tags' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Upload nouvelle cover image si fournie
        if ($request->hasFile('cover_image')) {
            // Supprimer l'ancienne
            if ($service->cover_image) {
                Storage::disk('public')->delete($service->cover_image);
            }
            $validated['cover_image'] = $request->file('cover_image')->store('services/covers', 'public');
        }

        // Upload nouvelles images portfolio si fournies
        if ($request->hasFile('portfolio')) {
            foreach ($request->file('portfolio') as $image) {
                $service->portfolios()->create([
                    'file_path' => $image->store('services/portfolio', 'public'),
                    'file_type' => 'image',
                ]);
            }
        }

        // Tags
        if (isset($validated['tags'])) {
            $validated['tags'] = array_map('trim', explode(',', $validated['tags']));
        }

        // Mettre à jour
        $service->update($validated);

        return redirect()
            ->route('prestataire.services.edit', $service)
            ->with('success', 'Service mis à jour avec succès !');
    }

    /**
     * Toggle service active status.
     */
    public function toggleActive(Service $service)
    {
        // Vérifier que le service appartient au prestataire
        if ($service->user_id !== Auth::id()) {
            abort(403);
        }

        $service->update([
            'is_active' => !$service->is_active
        ]);

        $status = $service->is_active ? 'activé' : 'désactivé';

        return redirect()
            ->back()
            ->with('success', "Service $status avec succès !");
    }

    /**
     * Sponsoriser un service (avantage du plan Premium) : un seul service
     * sponsorisé à la fois, choisir un nouveau désactive l'ancien.
     */
    public function toggleSponsored(Service $service)
    {
        if ($service->user_id !== Auth::id()) {
            abort(403);
        }

        if (Auth::user()->currentPlan()?->slug !== 'premium') {
            return redirect()
                ->back()
                ->with('error', 'La sponsorisation de service est réservée au plan Premium.');
        }

        if ($service->is_featured) {
            $service->update(['is_featured' => false]);

            return redirect()->back()->with('success', 'Service retiré de la mise en avant.');
        }

        Auth::user()->services()->update(['is_featured' => false]);
        $service->update(['is_featured' => true]);

        return redirect()->back()->with('success', 'Ce service est maintenant mis en avant !');
    }

    /**
     * Remove the specified service from storage.
     */
    public function destroy(Service $service)
    {
        // Vérifier que le service appartient au prestataire
        if ($service->user_id !== Auth::id()) {
            abort(403);
        }

        // Vérifier qu'il n'y a pas de commandes en cours
        $activeOrders = $service->orders()
            ->whereIn('status', ['pending_payment', 'paid', 'in_progress'])
            ->count();

        if ($activeOrders > 0) {
            return redirect()
                ->back()
                ->with('error', 'Impossible de supprimer un service avec des commandes en cours.');
        }

        // Supprimer les images
        if ($service->cover_image) {
            Storage::disk('public')->delete($service->cover_image);
        }

        foreach ($service->portfolios as $portfolio) {
            Storage::disk('public')->delete($portfolio->file_path);
        }

        $service->delete();

        return redirect()
            ->route('prestataire.services.index')
            ->with('success', 'Service supprimé avec succès !');
    }

    /**
     * Bloque la création si le prestataire a atteint le nombre de services
     * autorisé par son plan d'abonnement (null = illimité).
     */
    private function guardServiceLimit(): void
    {
        $maxServices = Auth::user()->maxServices();

        if ($maxServices !== null && Auth::user()->services()->count() >= $maxServices) {
            throw new \Illuminate\Http\Exceptions\HttpResponseException(redirect()
                ->route('prestataire.services.index')
                ->with('error', "Vous avez atteint la limite de {$maxServices} service(s) de votre plan actuel. Passez à un plan supérieur pour en publier davantage."));
        }
    }
}