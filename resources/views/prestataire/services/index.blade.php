<x-app-layout>
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="container mx-auto px-4">
            {{-- Header --}}
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div>
                    <h1 class="text-4xl font-black text-gray-900 mb-2"><x-app-icon name="box" class="w-8 h-8 inline-block" /> Mes services</h1>
                    <p class="text-gray-600">Gérez vos offres de services</p>
                </div>
                <a href="{{ route('prestataire.services.create') }}" 
                   class="bg-gradient-to-r from-yellow-400 to-yellow-500 hover:from-yellow-500 hover:to-yellow-600 text-blue-900 font-black px-6 py-3 rounded-full transition transform hover:scale-105 shadow-lg">
                    + Créer un service
                </a>
            </div>

            {{-- Filtres --}}
            <div class="bg-white rounded-3xl p-6 shadow-lg mb-8">
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('prestataire.services.index', ['status' => 'all']) }}" 
                       class="px-6 py-3 rounded-full font-bold transition {{ $status === 'all' ? 'bg-blue-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Tous ({{ Auth::user()->services->count() }})
                    </a>
                    <a href="{{ route('prestataire.services.index', ['status' => 'active']) }}" 
                       class="px-6 py-3 rounded-full font-bold transition {{ $status === 'active' ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Actifs ({{ Auth::user()->services()->where('is_active', true)->where('status', 'active')->count() }})
                    </a>
                    <a href="{{ route('prestataire.services.index', ['status' => 'inactive']) }}" 
                       class="px-6 py-3 rounded-full font-bold transition {{ $status === 'inactive' ? 'bg-gray-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Inactifs ({{ Auth::user()->services()->where('is_active', false)->count() }})
                    </a>
                </div>
            </div>

            {{-- Liste des services --}}
            @if($services->count() > 0)
                <div class="space-y-4">
                    @foreach($services as $service)
                        <div class="bg-white rounded-3xl shadow-lg overflow-hidden hover:shadow-xl transition">
                            <div class="flex flex-col md:flex-row">
                                {{-- Image --}}
                                <div class="md:w-64 h-48 md:h-auto relative flex-shrink-0">
                                    @if($service->cover_image)
                                        @if(Str::startsWith($service->cover_image, 'http'))
                                            <img src="{{ $service->cover_image }}" 
                                                 alt="{{ $service->title }}"
                                                 class="w-full h-full object-cover">
                                        @else
                                            <img src="{{ Storage::url($service->cover_image) }}" 
                                                 alt="{{ $service->title }}"
                                                 class="w-full h-full object-cover">
                                        @endif
                                    @else
                                        <div class="w-full h-full bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center">
                                            <x-app-icon name="box" class="w-12 h-12" />
                                        </div>
                                    @endif

                                    {{-- Badge statut --}}
                                    <span class="absolute top-3 right-3 px-3 py-1 rounded-full text-xs font-bold {{ $service->is_active ? 'bg-green-500 text-white' : 'bg-gray-500 text-white' }}">
                                        @if($service->is_active)
                                            <x-app-icon name="check" class="w-3 h-3 inline-block align-text-bottom" /> Actif
                                        @else
                                            Inactif
                                        @endif
                                    </span>
                                </div>

                                {{-- Contenu --}}
                                <div class="flex-1 p-6">
                                    <div class="flex justify-between items-start mb-4">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-2">
                                                <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-bold rounded-full">
                                                    {{ $service->category->name }}
                                                </span>
                                                @if($service->is_featured)
                                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-bold rounded-full">
                                                        <x-app-icon name="star" class="w-4 h-4 inline-block align-text-bottom" /> En vedette
                                                    </span>
                                                @endif
                                            </div>
                                            <h3 class="text-2xl font-black text-gray-900 mb-2">{{ $service->title }}</h3>
                                            <p class="text-gray-600 mb-3 line-clamp-2">{{ $service->description }}</p>
                                        </div>
                                    </div>

                                    {{-- Stats --}}
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                                        <div class="text-center p-3 bg-gray-50 rounded-xl">
                                            <div class="text-2xl font-black text-blue-900">{{ number_format($service->price, 0) }}</div>
                                            <div class="text-xs text-gray-600">FCFA</div>
                                        </div>
                                        <div class="text-center p-3 bg-gray-50 rounded-xl">
                                            <div class="text-2xl font-black text-green-600">{{ $service->total_orders }}</div>
                                            <div class="text-xs text-gray-600">Commandes</div>
                                        </div>
                                        <div class="text-center p-3 bg-gray-50 rounded-xl">
                                            <div class="text-2xl font-black text-yellow-600">{{ number_format($service->rating, 1) }}</div>
                                            <div class="text-xs text-gray-600">Note moyenne</div>
                                        </div>
                                        <div class="text-center p-3 bg-gray-50 rounded-xl">
                                            <div class="text-2xl font-black text-purple-600">{{ $service->total_reviews }}</div>
                                            <div class="text-xs text-gray-600">Avis</div>
                                        </div>
                                    </div>

                                    {{-- Actions --}}
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('services.show', $service->slug) }}" 
                                           target="_blank"
                                           class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold px-4 py-2 rounded-xl transition text-sm">
                                            <x-app-icon name="eye" class="w-4 h-4 inline-block align-text-bottom" /> Voir
                                        </a>
                                        <a href="{{ route('prestataire.services.edit', $service) }}" 
                                           class="flex-1 text-center bg-blue-900 hover:bg-blue-800 text-white font-bold px-4 py-2 rounded-xl transition text-sm">
                                            <x-app-icon name="pencil" class="w-4 h-4 inline-block align-text-bottom" /> Modifier
                                        </a>
                                        <form action="{{ route('prestataire.services.toggle', $service) }}" 
                                              method="POST" 
                                              class="flex-1">
                                            @csrf
                                            <button type="submit" 
                                                    class="w-full {{ $service->is_active ? 'bg-gray-500 hover:bg-gray-600' : 'bg-green-500 hover:bg-green-600' }} text-white font-bold px-4 py-2 rounded-xl transition text-sm">
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
                                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce service ?')">
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
                <div class="bg-white rounded-3xl p-16 text-center shadow-lg">
                    <div class="mb-6"><x-app-icon name="box" class="w-16 h-16" /></div>
                    <h3 class="text-2xl font-black text-gray-900 mb-4">
                        @if($status === 'active')
                            Aucun service actif
                        @elseif($status === 'inactive')
                            Aucun service inactif
                        @else
                            Aucun service créé
                        @endif
                    </h3>
                    <p class="text-gray-600 mb-8">
                        @if($status === 'all')
                            Commencez par créer votre premier service pour attirer des clients !
                        @else
                            Changez de filtre pour voir vos autres services.
                        @endif
                    </p>
                    @if($status === 'all')
                        <a href="{{ route('prestataire.services.create') }}" 
                           class="inline-block bg-gradient-to-r from-yellow-400 to-yellow-500 hover:from-yellow-500 hover:to-yellow-600 text-blue-900 font-black px-8 py-4 rounded-full transition transform hover:scale-105 shadow-lg">
                            + Créer mon premier service
                        </a>
                    @else
                        <a href="{{ route('prestataire.services.index', ['status' => 'all']) }}" 
                           class="inline-block bg-blue-900 hover:bg-blue-800 text-white font-bold px-8 py-4 rounded-full transition">
                            Voir tous mes services
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-app-layout>