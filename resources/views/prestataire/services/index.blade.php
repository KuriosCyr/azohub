<x-app-layout>
    @php
        $slotsFull = $slotsMax !== null && $slotsUsed >= $slotsMax;
    @endphp
    <div class="min-h-screen bg-cream py-8">
        <div class="container mx-auto px-4">
            {{-- Header --}}
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div>
                    <h1 class="text-4xl font-serif font-medium text-ink-900 mb-2"><x-app-icon name="box" class="w-8 h-8 inline-block" /> Mes services</h1>
                    <p class="text-ink-500">
                        Gérez vos offres de services —
                        <span class="font-semibold {{ $slotsFull ? 'text-red-600' : 'text-ink-700' }}">
                            {{ $slotsMax === null ? $slotsUsed . ' service(s), sans limite' : $slotsUsed . '/' . $slotsMax . ' services utilisés' }}
                        </span>
                    </p>
                </div>

                @if($slotsFull)
                    <div class="text-left md:text-right">
                        <span class="inline-block cursor-not-allowed bg-ink-200 text-ink-500 font-bold px-6 py-3 rounded-lg" title="Limite de services atteinte">
                            + Créer un service
                        </span>
                        <p class="mt-2 max-w-xs text-xs text-ink-500 md:ml-auto">
                            Limite atteinte. Supprimez un service (les services refusés ne comptent pas) ou
                            <a href="{{ route('prestataire.subscription') }}" class="font-bold text-terracotta-600 underline">passez à un plan supérieur</a>.
                        </p>
                    </div>
                @else
                    <a href="{{ route('prestataire.services.create') }}"
                       class="bg-terracotta-600 hover:bg-terracotta-700 text-cream-50 font-bold px-6 py-3 rounded-lg transition shadow-md">
                        + Créer un service
                    </a>
                @endif
            </div>

            {{-- Filtres --}}
            <div class="bg-cream-50 rounded-xl p-4 md:p-6 border border-ink-100 mb-8">
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('prestataire.services.index', ['status' => 'all']) }}"
                       class="px-5 py-2.5 rounded-full font-bold text-sm transition {{ $status === 'all' ? 'bg-ink-900 text-cream-50' : 'bg-ink-100/30 text-ink-700 hover:bg-ink-100/50' }}">
                        Tous ({{ Auth::user()->services()->count() }})
                    </a>
                    <a href="{{ route('prestataire.services.index', ['status' => 'active']) }}"
                       class="px-5 py-2.5 rounded-full font-bold text-sm transition {{ $status === 'active' ? 'bg-forest-600 text-cream-50' : 'bg-ink-100/30 text-ink-700 hover:bg-ink-100/50' }}">
                        Actifs ({{ Auth::user()->services()->where('is_active', true)->where('status', 'active')->count() }})
                    </a>
                    <a href="{{ route('prestataire.services.index', ['status' => 'inactive']) }}"
                       class="px-5 py-2.5 rounded-full font-bold text-sm transition {{ $status === 'inactive' ? 'bg-ink-500 text-cream-50' : 'bg-ink-100/30 text-ink-700 hover:bg-ink-100/50' }}">
                        Inactifs ({{ Auth::user()->services()->where('is_active', false)->count() }})
                    </a>
                    <a href="{{ route('prestataire.services.index', ['status' => 'pending']) }}"
                       class="px-5 py-2.5 rounded-full font-bold text-sm transition {{ $status === 'pending' ? 'bg-ochre-500 text-ink-900' : 'bg-ink-100/30 text-ink-700 hover:bg-ink-100/50' }}">
                        En modération ({{ Auth::user()->services()->whereIn('status', ['pending', 'rejected'])->count() }})
                    </a>
                </div>
            </div>

            {{-- Liste des services --}}
            @if($services->count() > 0)
                <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach($services as $service)
                        @php
                            $badge = match (true) {
                                $service->status === 'pending' => ['bg-ochre-500 text-ink-900', 'En modération'],
                                $service->status === 'rejected' => ['bg-red-600 text-white', 'Refusé'],
                                $service->status !== 'active' => ['bg-ink-500 text-cream-50', $service->status_label],
                                $service->is_active => ['bg-forest-600 text-cream-50', 'Actif'],
                                default => ['bg-ink-500 text-cream-50', 'Inactif'],
                            };
                        @endphp
                        <div x-data x-intersect.once="$el.classList.add('revealed')"
                             style="transition-delay: {{ $loop->index * 60 }}ms"
                             class="reveal flex flex-col bg-cream-50 rounded-xl border border-ink-100 hover:shadow-md transition">
                            {{-- Image + badge de statut --}}
                            <div class="relative h-44 flex-shrink-0 overflow-hidden rounded-t-xl">
                                <x-service-cover :service="$service" class="w-full h-full object-cover" />
                                <span class="absolute top-3 right-3 px-3 py-1 rounded-full text-xs font-bold {{ $badge[0] }}">{{ $badge[1] }}</span>
                            </div>

                            {{-- Contenu --}}
                            <div class="flex flex-1 flex-col p-5">
                                <div class="mb-2 flex flex-wrap items-center gap-2">
                                    <span class="px-3 py-1 bg-terracotta-50 text-terracotta-700 text-xs font-bold rounded-full">
                                        {{ $service->category->name }}
                                    </span>
                                    @if($service->is_featured)
                                        <span class="px-3 py-1 bg-ochre-500/15 text-ink-900 text-xs font-bold rounded-full">
                                            <x-app-icon name="star" class="w-3.5 h-3.5 inline-block align-text-bottom" /> Sponsorisé
                                        </span>
                                    @endif
                                </div>

                                <h3 class="mb-3 line-clamp-2 text-lg font-bold leading-snug text-ink-900" title="{{ $service->title }}">{{ $service->title }}</h3>

                                @if($service->status === 'pending')
                                    <p class="mb-3 rounded-lg bg-ochre-500/15 px-3 py-2 text-xs text-ink-700">En cours de modération : pas encore visible par les clients.</p>
                                @elseif($service->status === 'rejected')
                                    <p class="mb-3 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700"><strong>Refusé.</strong> {{ $service->moderation_note ?: 'Aucun motif précisé.' }} Modifiez-le pour le soumettre à nouveau.</p>
                                @endif

                                {{-- Stats sur une ligne --}}
                                <div class="mt-auto mb-4 grid grid-cols-3 divide-x divide-ink-100 rounded-lg bg-ink-100/30 py-2 text-center">
                                    <div>
                                        <div class="text-sm font-bold text-ink-900">{{ number_format($service->price, 0, ',', ' ') }}</div>
                                        <div class="text-[11px] text-ink-500">FCFA</div>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-forest-700">{{ $service->total_orders }}</div>
                                        <div class="text-[11px] text-ink-500">Commandes</div>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-ochre-600">{{ number_format($service->rating, 1) }} <span class="font-normal text-ink-400">({{ $service->total_reviews }})</span></div>
                                        <div class="text-[11px] text-ink-500">Note</div>
                                    </div>
                                </div>

                                {{-- Actions --}}
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('prestataire.services.edit', $service) }}"
                                       class="flex-1 text-center bg-ink-900 hover:bg-ink-700 text-cream-50 font-bold px-3 py-2 rounded-xl transition text-sm">
                                        <x-app-icon name="pencil" class="w-4 h-4 inline-block align-text-bottom" /> Modifier
                                    </a>
                                    <a href="{{ route('services.show', $service->slug) }}" target="_blank"
                                       class="bg-ink-100/30 hover:bg-ink-100/50 text-ink-700 font-bold px-3 py-2 rounded-xl transition text-sm"
                                       title="Voir la page publique{{ $service->status === 'active' ? '' : ' (aperçu, visible par vous seul)' }}">
                                        <x-app-icon name="eye" class="w-4 h-4 inline-block align-text-bottom" /> Voir
                                    </a>

                                    {{-- Menu « plus d'actions » --}}
                                    <div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
                                        <button type="button" @click="open = !open" aria-label="Plus d'actions" :aria-expanded="open"
                                                class="flex h-9 w-9 items-center justify-center rounded-xl bg-ink-100/30 text-ink-700 transition hover:bg-ink-100/50">
                                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <circle cx="4" cy="10" r="1.6" /><circle cx="10" cy="10" r="1.6" /><circle cx="16" cy="10" r="1.6" />
                                            </svg>
                                        </button>
                                        <div x-show="open" x-transition style="display: none;"
                                             class="absolute bottom-full right-0 z-20 mb-2 w-56 rounded-xl border border-ink-100 bg-cream-50 p-1.5 shadow-lg">
                                            @if(Auth::user()->currentPlan()?->slug === 'premium')
                                                <form action="{{ route('prestataire.services.sponsor', $service) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm font-semibold text-ink-700 hover:bg-ink-100/40">
                                                        <x-app-icon name="sparkles" class="w-4 h-4" />
                                                        {{ $service->is_featured ? 'Retirer la sponsorisation' : 'Sponsoriser' }}
                                                    </button>
                                                </form>
                                            @endif
                                            <form action="{{ route('prestataire.services.toggle', $service) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm font-semibold text-ink-700 hover:bg-ink-100/40">
                                                    @if($service->is_active)
                                                        <x-app-icon name="x-circle" class="w-4 h-4" /> Désactiver
                                                    @else
                                                        <x-app-icon name="check-circle" class="w-4 h-4" /> Activer
                                                    @endif
                                                </button>
                                            </form>
                                            <form action="{{ route('prestataire.services.destroy', $service) }}" method="POST"
                                                  x-data
                                                  @submit.prevent="confirmAction('Êtes-vous sûr de vouloir supprimer ce service ? Cette action est irréversible.', { danger: true, confirmText: 'Supprimer' }).then(ok => ok && $el.submit())">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm font-semibold text-red-600 hover:bg-red-50">
                                                    <x-app-icon name="trash" class="w-4 h-4" /> Supprimer
                                                </button>
                                            </form>
                                        </div>
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

    @if(session('success') || session('error'))
        @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                window.notifyAction(@json(session('success') ?? session('error')), {
                    icon: @json(session('success') ? 'success' : 'error'),
                });
            });
        </script>
        @endpush
    @endif
</x-app-layout>
