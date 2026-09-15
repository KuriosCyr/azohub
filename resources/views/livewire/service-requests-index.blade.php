<div class="py-8 bg-cream min-h-screen">
    <div class="container mx-auto px-4">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-4xl font-serif font-medium text-ink-900 mb-2">
                    {{ $mine ? 'Mes demandes' : 'Demandes ouvertes' }}
                </h1>
                <p class="text-ink-500">
                    @if($mine)
                        Suivez les propositions reçues sur vos demandes.
                    @else
                        {{ $serviceRequests->total() }} demande(s) en attente de proposition
                    @endif
                </p>
            </div>

            <div class="flex items-center gap-3">
                @auth
                    @if(!Auth::user()->isPrestataire())
                        <a href="{{ route('service-requests.index', ['mine' => 1]) }}"
                           class="px-4 py-2.5 rounded-lg font-semibold text-sm transition {{ $mine ? 'bg-ink-900 text-cream-50' : 'bg-cream-50 border border-ink-200 text-ink-700 hover:border-terracotta-600/40' }}">
                            Mes demandes
                        </a>
                        <a href="{{ route('service-requests.index') }}"
                           class="px-4 py-2.5 rounded-lg font-semibold text-sm transition {{ !$mine ? 'bg-ink-900 text-cream-50' : 'bg-cream-50 border border-ink-200 text-ink-700 hover:border-terracotta-600/40' }}">
                            Demandes ouvertes
                        </a>
                        <a href="{{ route('service-requests.create') }}"
                           class="bg-terracotta-600 hover:bg-terracotta-700 text-cream-50 font-bold px-5 py-2.5 rounded-lg transition shadow-md">
                            + Nouvelle demande
                        </a>
                    @endif
                @endauth
            </div>
        </div>

        @if(!$mine)
            {{-- Filtres --}}
            <div class="bg-cream-50 rounded-lg p-4 border border-ink-100 mb-8 flex flex-wrap gap-4 items-center">
                <select wire:model.live="categoryFilter" class="px-4 py-2.5 rounded-lg border border-ink-200 text-sm font-semibold focus:ring-2 focus:ring-terracotta-600 focus:border-transparent">
                    <option value="">Toutes les catégories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                <input type="text" wire:model.live.debounce.400ms="cityFilter" placeholder="Filtrer par ville..."
                       class="px-4 py-2.5 rounded-lg border border-ink-200 text-sm focus:ring-2 focus:ring-terracotta-600 focus:border-transparent">
            </div>
        @endif

        <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-6">
            @forelse($serviceRequests as $request)
                <a href="{{ route('service-requests.show', $request) }}"
                   class="bg-cream-50 rounded-xl overflow-hidden border border-ink-100 hover:shadow-md transition-all duration-300 p-6 flex flex-col">
                    <div class="flex items-center justify-between mb-3">
                        <span class="bg-terracotta-50 text-terracotta-700 px-3 py-1 rounded-full text-xs font-bold uppercase">
                            {{ $request->category->name }}
                        </span>
                        <span class="text-xs font-semibold px-2 py-1 rounded-full
                            {{ $request->status === 'open' ? 'bg-forest-600/10 text-forest-700' : ($request->status === 'closed' ? 'bg-ink-100 text-ink-500' : 'bg-red-50 text-red-600') }}">
                            {{ $request->status === 'open' ? 'Ouverte' : ($request->status === 'closed' ? 'Clôturée' : 'Annulée') }}
                        </span>
                    </div>

                    <h3 class="font-bold text-lg mb-2 line-clamp-2 text-ink-900">{{ $request->title }}</h3>
                    <p class="text-sm text-ink-500 line-clamp-3 mb-4 flex-1">{{ $request->description }}</p>

                    <div class="flex items-center justify-between text-sm pt-4 border-t border-ink-100">
                        <span class="text-ink-400 flex items-center gap-1">
                            <x-app-icon name="map-pin" class="w-4 h-4" />
                            {{ $request->city }}
                        </span>
                        <span class="font-semibold text-ink-700">
                            {{ $request->proposals_count }} proposition(s)
                        </span>
                    </div>

                    @if($request->budget)
                        <div class="mt-3 text-right">
                            <span class="text-xs text-ink-400">Budget indicatif</span>
                            <p class="font-bold text-ink-900">{{ number_format($request->budget, 0) }} FCFA</p>
                        </div>
                    @endif
                </a>
            @empty
                <div class="col-span-full text-center py-16">
                    <div class="w-20 h-20 rounded-full bg-ink-100/30 flex items-center justify-center mx-auto mb-4">
                        <x-app-icon name="clipboard" class="w-10 h-10 text-ink-300" />
                    </div>
                    <h3 class="text-2xl font-bold text-ink-900 mb-2">
                        {{ $mine ? 'Vous n\'avez publié aucune demande' : 'Aucune demande ouverte pour le moment' }}
                    </h3>
                    <p class="text-ink-500 mb-6">
                        @if($mine)
                            Publiez une demande pour recevoir des propositions de prestataires.
                        @else
                            Revenez bientôt ou publiez votre propre demande.
                        @endif
                    </p>
                    @auth
                        @if(!Auth::user()->isPrestataire())
                            <a href="{{ route('service-requests.create') }}" class="inline-block bg-ink-900 hover:bg-ink-700 text-cream-50 font-bold px-6 py-3 rounded-lg transition">
                                Publier une demande
                            </a>
                        @endif
                    @endauth
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $serviceRequests->links() }}
        </div>
    </div>
</div>
