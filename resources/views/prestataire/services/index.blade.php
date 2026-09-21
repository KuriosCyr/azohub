<x-app-layout>
    <div class="min-h-screen bg-cream py-8">
        <div class="container mx-auto px-4">
            {{-- Header --}}
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div>
                    <h1 class="text-4xl font-serif font-medium text-ink-900 mb-2"><x-app-icon name="box" class="w-8 h-8 inline-block" /> Mes services</h1>
                    <p class="text-ink-500">Gérez vos offres de services</p>
                </div>
                <a href="{{ route('prestataire.services.create') }}"
                   class="bg-terracotta-600 hover:bg-terracotta-700 text-cream-50 font-bold px-6 py-3 rounded-lg transition shadow-md">
                    + Créer un service
                </a>
            </div>

            {{-- Filtres --}}
            <div class="bg-cream-50 rounded-xl p-6 border border-ink-100 mb-8">
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('prestataire.services.index', ['status' => 'all']) }}"
                       class="px-6 py-3 rounded-full font-bold transition {{ $status === 'all' ? 'bg-ink-900 text-cream-50' : 'bg-ink-100/30 text-ink-700 hover:bg-ink-100/50' }}">
                        Tous ({{ Auth::user()->services->count() }})
                    </a>
                    <a href="{{ route('prestataire.services.index', ['status' => 'active']) }}"
                       class="px-6 py-3 rounded-full font-bold transition {{ $status === 'active' ? 'bg-forest-600 text-cream-50' : 'bg-ink-100/30 text-ink-700 hover:bg-ink-100/50' }}">
                        Actifs ({{ Auth::user()->services()->where('is_active', true)->where('status', 'active')->count() }})
                    </a>
                    <a href="{{ route('prestataire.services.index', ['status' => 'inactive']) }}"
                       class="px-6 py-3 rounded-full font-bold transition {{ $status === 'inactive' ? 'bg-ink-500 text-cream-50' : 'bg-ink-100/30 text-ink-700 hover:bg-ink-100/50' }}">
                        Inactifs ({{ Auth::user()->services()->where('is_active', false)->count() }})
                    </a>
                    <a href="{{ route('prestataire.services.index', ['status' => 'pending']) }}"
                       class="px-6 py-3 rounded-full font-bold transition {{ $status === 'pending' ? 'bg-ochre-500 text-ink-900' : 'bg-ink-100/30 text-ink-700 hover:bg-ink-100/50' }}">
                        En modération ({{ Auth::user()->services()->whereIn('status', ['pending', 'rejected'])->count() }})
                    </a>
                </div>
            </div>

            {{-- Liste des services --}}
            @if($services->count() > 0)
                <div class="space-y-4">
                    @foreach($services as $service)
                        <div x-data x-intersect.once="$el.classList.add('revealed')"
                             style="transition-delay: {{ $loop->index * 60 }}ms"
                             class="reveal bg-cream-50 rounded-xl border border-ink-100 overflow-hidden hover:shadow-md transition">
                            <div class="flex flex-col md:flex-row">
                                {{-- Image --}}
                                <div class="md:w-64 h-48 md:h-auto relative flex-shrink-0">
                                    <x-service-cover :service="$service" class="w-full h-full object-cover" />

                                    {{-- Badge statut --}}
                                    @php
                                        $badge = match (true) {
                                            $service->status === 'pending' => ['bg-ochre-500 text-ink-900', 'En modération'],
                                            $service->status === 'rejected' => ['bg-red-600 text-white', 'Refusé'],
                                            $service->status !== 'active' => ['bg-ink-500 text-cream-50', $service->status_label],
                                            $service->is_active => ['bg-forest-600 text-cream-50', 'Actif'],
                                            default => ['bg-ink-500 text-cream-50', 'Inactif'],
                                        };
                                    @endphp
                                    <span class="absolute top-3 right-3 px-3 py-1 rounded-full text-xs font-bold {{ $badge[0] }}">{{ $badge[1] }}</span>
                                </div>

                                {{-- Contenu --}}
                                <div class="flex-1 p-6">
                                    <div class="flex justify-between items-start mb-4">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-2">
                                                <span class="px-3 py-1 bg-terracotta-50 text-terracotta-700 text-xs font-bold rounded-full">
                                                    {{ $service->category->name }}
                                                </span>
                                                @if($service->is_featured)
                                                    <span class="px-3 py-1 bg-ochre-500/15 text-ink-900 text-xs font-bold rounded-full">
                                                        <x-app-icon name="star" class="w-4 h-4 inline-block align-text-bottom" /> En vedette
                                                    </span>
                                                @endif
                                            </div>
                                            <h3 class="text-2xl font-bold text-ink-900 mb-2">{{ $service->title }}</h3>
                                            @if($service->status === 'pending')
                                                <p class="mb-3 rounded-lg bg-ochre-500/15 px-3 py-2 text-sm text-ink-700">Ce service est en cours de modération : il n'est pas encore visible par les clients.</p>
                                            @elseif($service->status === 'rejected')
                                                <p class="mb-3 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700"><strong>Service refusé.</strong> {{ $service->moderation_note ?: 'Aucun motif précisé.' }} Modifiez-le pour le soumettre à nouveau.</p>
                                            @endif
                                            <p class="text-ink-500 mb-3 line-clamp-2">{{ $service->description }}</p>
                                        </div>
                                    </div>

                                    {{-- Stats --}}
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                                        <div class="text-center p-3 bg-ink-100/30 rounded-xl">
                                            <div class="text-2xl font-bold text-ink-900">{{ number_format($service->price, 0, ',', ' ') }}</div>
                                            <div class="text-xs text-ink-500">FCFA</div>
                                        </div>
                                        <div class="text-center p-3 bg-ink-100/30 rounded-xl">
                                            <div class="text-2xl font-bold text-forest-700">{{ $service->total_orders }}</div>
                                            <div class="text-xs text-ink-500">Commandes</div>
                                        </div>
                                        <div class="text-center p-3 bg-ink-100/30 rounded-xl">
                                            <div class="text-2xl font-bold text-ochre-600">{{ number_format($service->rating, 1) }}</div>
                                            <div class="text-xs text-ink-500">Note moyenne</div>
                                        </div>
                                        <div class="text-center p-3 bg-ink-100/30 rounded-xl">
                                            <div class="text-2xl font-bold text-clay-500">{{ $service->total_reviews }}</div>
                                            <div class="text-xs text-ink-500">Avis</div>
                                        </div>
                                    </div>

                                    {{-- Actions --}}
                                    <div class="flex flex-wrap gap-2">
                                        @if(Auth::user()->currentPlan()?->slug === 'premium')
                                            <form action="{{ route('prestataire.services.sponsor', $service) }}" method="POST" class="flex-1">
                                                @csrf
                                                <button type="submit"
                                                        class="w-full {{ $service->is_featured ? 'bg-ochre-500 hover:bg-ochre-600 text-ink-900' : 'bg-ink-100/30 hover:bg-ink-100/50 text-ink-700' }} font-bold px-4 py-2 rounded-xl transition text-sm">
                                                    <x-app-icon name="sparkles" class="w-4 h-4 inline-block align-text-bottom" />
                                                    {{ $service->is_featured ? 'Sponsorisé' : 'Sponsoriser' }}
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('services.show', $service->slug) }}"
                                           target="_blank"
                                           class="flex-1 text-center bg-ink-100/30 hover:bg-ink-100/50 text-ink-700 font-bold px-4 py-2 rounded-xl transition text-sm">
                                            <x-app-icon name="eye" class="w-4 h-4 inline-block align-text-bottom" /> Voir
                                        </a>
                                        <a href="{{ route('prestataire.services.edit', $service) }}"
                                           class="flex-1 text-center bg-ink-900 hover:bg-ink-700 text-cream-50 font-bold px-4 py-2 rounded-xl transition text-sm">
                                            <x-app-icon name="pencil" class="w-4 h-4 inline-block align-text-bottom" /> Modifier
                                        </a>
                                        <form action="{{ route('prestataire.services.toggle', $service) }}"
                                              method="POST"
                                              class="flex-1">
                                            @csrf
                                            <button type="submit"
                                                    class="w-full {{ $service->is_active ? 'bg-ink-500 hover:bg-ink-700' : 'bg-forest-600 hover:bg-forest-700' }} text-cream-50 font-bold px-4 py-2 rounded-xl transition text-sm">
                                                @if($service->is_active)
                                                    <x-app-icon name="x-circle" class="w-4 h-4 inline-block align-text-bottom" /> Désactiver
                                                @else
                                                    <x-app-icon name="check-circle" class="w-4 h-4 inline-block align-text-bottom" /> Activer
                                                @endif
                                            </button>
                                        </form>
                                        <form action="{{ route('prestataire.services.destroy', $service) }}"
                                              method="POST"
                                              class="flex-1"
                                              x-data
                                              @submit.prevent="confirmAction('Êtes-vous sûr de vouloir supprimer ce service ? Cette action est irréversible.', { danger: true, confirmText: 'Supprimer' }).then(ok => ok && $el.submit())">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="w-full bg-red-500 hover:bg-red-600 text-white font-bold px-4 py-2 rounded-xl transition text-sm">
                                                <x-app-icon name="trash" class="w-4 h-4 inline-block align-text-bottom" /> Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-8">
                    {{ $services->links() }}
                </div>
            @else
                {{-- Empty state --}}
                <div class="bg-cream-50 rounded-xl p-16 text-center border border-ink-100">
                    <div class="mb-6"><x-app-icon name="box" class="w-16 h-16" /></div>
                    <h3 class="text-2xl font-bold text-ink-900 mb-4">
                        @if($status === 'active')
                            Aucun service actif
                        @elseif($status === 'inactive')
                            Aucun service inactif
                        @elseif($status === 'pending')
                            Aucun service en modération
                        @else
                            Aucun service créé
                        @endif
                    </h3>
                    <p class="text-ink-500 mb-8">
                        @if($status === 'all')
                            Commencez par créer votre premier service pour attirer des clients !
                        @else
                            Changez de filtre pour voir vos autres services.
                        @endif
                    </p>
                    @if($status === 'all')
                        <a href="{{ route('prestataire.services.create') }}"
                           class="inline-block bg-terracotta-600 hover:bg-terracotta-700 text-cream-50 font-bold px-8 py-4 rounded-lg transition shadow-md">
                            + Créer mon premier service
                        </a>
                    @else
                        <a href="{{ route('prestataire.services.index', ['status' => 'all']) }}"
                           class="inline-block bg-ink-900 hover:bg-ink-700 text-cream-50 font-bold px-8 py-4 rounded-lg transition">
                            Voir tous mes services
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-app-layout>