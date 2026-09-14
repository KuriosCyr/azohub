<x-app-layout>
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="container mx-auto px-4 max-w-5xl">
            {{-- Header --}}
            <div class="mb-8">
                <a href="{{ route('prestataire.services.index') }}" class="text-blue-900 hover:text-blue-700 font-bold mb-4 inline-block">
                    ← Retour à mes services
                </a>
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <h1 class="text-4xl font-black text-gray-900 mb-2">{{ $service->title }}</h1>
                        <p class="text-gray-600">{{ $service->category->name }}</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('services.show', $service->slug) }}" target="_blank"
                           class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold px-4 py-2 rounded-xl transition text-sm">
                            <x-app-icon name="eye" class="w-4 h-4 inline-block align-text-bottom" /> Voir la page publique
                        </a>
                        <a href="{{ route('prestataire.services.edit', $service) }}"
                           class="bg-blue-900 hover:bg-blue-800 text-white font-bold px-4 py-2 rounded-xl transition text-sm">
                            <x-app-icon name="pencil" class="w-4 h-4 inline-block align-text-bottom" /> Modifier
                        </a>
                    </div>
                </div>
            </div>

            {{-- Image de couverture --}}
            <div class="bg-white rounded-3xl overflow-hidden shadow-lg mb-8">
                <div class="h-64 relative">
                    <x-service-cover :service="$service" class="w-full h-full object-cover" />
                    <span class="absolute top-4 right-4 px-4 py-2 rounded-full text-sm font-bold {{ $service->is_active ? 'bg-green-500 text-white' : 'bg-gray-500 text-white' }}">
                        @if($service->is_active)
                            <x-app-icon name="check" class="w-4 h-4 inline-block align-text-bottom" /> Actif
                        @else
                            Inactif
                        @endif
                    </span>
                </div>
                <div class="p-8">
                    <p class="text-gray-700 whitespace-pre-line">{{ $service->description }}</p>
                </div>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white rounded-2xl p-6 text-center shadow-lg">
                    <div class="text-3xl font-black text-blue-900">{{ number_format($service->price, 0) }}</div>
                    <div class="text-xs text-gray-600 mt-1">FCFA</div>
                </div>
                <div class="bg-white rounded-2xl p-6 text-center shadow-lg">
                    <div class="text-3xl font-black text-green-600">{{ $service->total_orders }}</div>
                    <div class="text-xs text-gray-600 mt-1">Commandes</div>
                </div>
                <div class="bg-white rounded-2xl p-6 text-center shadow-lg">
                    <div class="text-3xl font-black text-yellow-600">{{ number_format($service->rating, 1) }}</div>
                    <div class="text-xs text-gray-600 mt-1">Note moyenne</div>
                </div>
                <div class="bg-white rounded-2xl p-6 text-center shadow-lg">
                    <div class="text-3xl font-black text-purple-600">{{ $service->total_reviews }}</div>
                    <div class="text-xs text-gray-600 mt-1">Avis</div>
                </div>
            </div>

            {{-- Portfolio --}}
            @if($service->portfolios->count() > 0)
                <div class="bg-white rounded-3xl p-8 shadow-lg mb-8">
                    <h2 class="text-2xl font-black text-gray-900 mb-6"><x-app-icon name="photo" class="w-6 h-6 inline-block" /> Portfolio</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach($service->portfolios as $portfolio)
                            <img src="{{ Storage::url($portfolio->file_path) }}"
                                 alt="{{ $portfolio->title ?? $service->title }}"
                                 class="w-full h-32 object-cover rounded-xl">
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Dernières commandes --}}
            <div class="bg-white rounded-3xl p-8 shadow-lg mb-8">
                <h2 class="text-2xl font-black text-gray-900 mb-6"><x-app-icon name="cart" class="w-6 h-6 inline-block" /> Dernières commandes</h2>
                @if($service->orders->count() > 0)
                    <div class="space-y-3">
                        @foreach($service->orders->sortByDesc('created_at')->take(10) as $order)
                            <a href="{{ route('orders.show', $order) }}"
                               class="flex items-center justify-between p-4 bg-gray-50 hover:bg-gray-100 rounded-xl transition">
                                <div>
                                    <p class="font-bold text-gray-900">{{ $order->order_number }}</p>
                                    <p class="text-sm text-gray-600">{{ $order->client->name }} · {{ $order->created_at->format('d/m/Y') }}</p>
                                </div>
                                <span class="text-sm font-bold text-blue-900">{{ $order->status_label }}</span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500">Aucune commande pour ce service pour le moment.</p>
                @endif
            </div>

            {{-- Avis --}}
            <div class="bg-white rounded-3xl p-8 shadow-lg">
                <h2 class="text-2xl font-black text-gray-900 mb-6"><x-app-icon name="star" class="w-6 h-6 inline-block" /> Avis clients</h2>
                @if($service->reviews->count() > 0)
                    <div class="space-y-4">
                        @foreach($service->reviews->sortByDesc('created_at') as $review)
                            <div class="p-4 bg-gray-50 rounded-xl">
                                <div class="flex items-center justify-between mb-2">
                                    <p class="font-bold text-gray-900">{{ $review->reviewer->name }}</p>
                                    <x-star-rating :rating="$review->rating" class="w-4 h-4" />
                                </div>
                                @if($review->comment)
                                    <p class="text-gray-700">{{ $review->comment }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500">Aucun avis pour ce service pour le moment.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
