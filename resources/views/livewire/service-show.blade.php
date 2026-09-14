<div class="py-8 bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4">
        {{-- Breadcrumb --}}
        <nav class="mb-6 text-sm">
            <ol class="flex items-center gap-2 text-gray-600">
                <li><a href="{{ route('home') }}" class="hover:text-blue-600">Accueil</a></li>
                <li>→</li>
                <li><a href="{{ route('services.index') }}" class="hover:text-blue-600">Services</a></li>
                <li>→</li>
                <li><a href="{{ route('services.index', ['category' => $service->category->slug]) }}" class="hover:text-blue-600">{{ $service->category->name }}</a></li>
                <li>→</li>
                <li class="text-gray-900 font-semibold">{{ Str::limit($service->title, 50) }}</li>
            </ol>
        </nav>

        <div class="grid lg:grid-cols-3 gap-8">
            {{-- Colonne principale --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- En-tête du service --}}
                <div class="bg-white rounded-3xl p-8 shadow-lg">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <span class="inline-block bg-yellow-400 text-blue-900 px-4 py-1 rounded-full text-sm font-bold uppercase mb-3">
                                {{ $service->category->name }}
                            </span>
                            <h1 class="text-4xl font-black text-gray-900 mb-4">
                                {{ $service->title }}
                            </h1>
                        </div>
                    </div>

                    {{-- Prestataire --}}
                    <div class="flex items-center gap-4 pb-6 border-b">
                        <img src="{{ $service->prestataire->avatar ? Storage::url($service->prestataire->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($service->prestataire->name) }}" 
                             alt="{{ $service->prestataire->name }}"
                             class="w-16 h-16 rounded-full border-4 border-blue-100">
                        <div class="flex-1">
                            <a href="#" class="text-xl font-bold text-gray-900 hover:text-blue-600">
                                {{ $service->prestataire->name }}
                            </a>
                            @if($service->prestataire->level === 'expert')
                                <span class="ml-2 text-yellow-600 text-sm inline-flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    Expert
                                </span>
                            @endif
                            <div class="flex items-center gap-4 text-sm text-gray-600 mt-1">
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    {{ number_format($service->prestataire->rating, 1) }} ({{ $service->prestataire->completed_orders }} commandes)
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    {{ $service->prestataire->city }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Stats rapides --}}
                    <div class="grid grid-cols-3 gap-4 py-6 border-b">
                        <div class="text-center">
                            <p class="text-3xl font-black text-blue-900">{{ $service->orders_count }}</p>
                            <p class="text-sm text-gray-600">Commandes</p>
                        </div>
                        <div class="text-center">
                            <p class="text-3xl font-black text-blue-900 flex items-center justify-center gap-1">
                                {{ number_format($service->rating, 1) }}
                                <svg class="w-6 h-6 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            </p>
                            <p class="text-sm text-gray-600">Note moyenne</p>
                        </div>
                        <div class="text-center">
                            <p class="text-3xl font-black text-blue-900">{{ $service->delivery_time }}j</p>
                            <p class="text-sm text-gray-600">Délai</p>
                        </div>
                    </div>
                </div>

                {{-- Galerie d'images --}}
                @if($service->cover_image || $service->portfolios->count() > 0)
                <div class="bg-white rounded-3xl p-8 shadow-lg">
                    <h2 class="text-2xl font-black mb-6">Galerie</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        {{-- Image principale --}}
                        @if($service->cover_image)
                        <div class="col-span-2 md:col-span-3 relative h-80 rounded-2xl overflow-hidden group">
                            <img src="{{ Storage::url($service->cover_image) }}" 
                                 alt="{{ $service->title }}"
                                 class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                        </div>
                        @endif

                        {{-- Portfolio --}}
                        @foreach($service->portfolios as $portfolio)
                        <div class="relative h-48 rounded-2xl overflow-hidden group cursor-pointer">
                            <img src="{{ Storage::url($portfolio->file_path) }}" 
                                 alt="Portfolio"
                                 class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Description --}}
                <div class="bg-white rounded-3xl p-8 shadow-lg">
                    <h2 class="text-2xl font-black mb-6">À propos de ce service</h2>
                    <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed">
                        {!! nl2br(e($service->description)) !!}
                    </div>

                    @if($service->tags && count($service->tags) > 0)
                    <div class="mt-6 pt-6 border-t">
                        <p class="text-sm font-bold text-gray-700 mb-3">Tags:</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($service->tags as $tag)
                            <span class="bg-blue-50 text-blue-700 px-4 py-2 rounded-full text-sm font-semibold">
                                #{{ $tag }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Avis clients --}}
                @if($service->reviews->count() > 0)
                <div class="bg-white rounded-3xl p-8 shadow-lg">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-black">Avis clients ({{ $service->total_reviews }})</h2>
                        <div class="text-right">
                            <p class="text-3xl font-black text-blue-900 flex items-center justify-center gap-1">
                                {{ number_format($service->rating, 1) }}
                                <svg class="w-6 h-6 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            </p>
                            <p class="text-sm text-gray-600">Note moyenne</p>
                        </div>
                    </div>

                    <div class="space-y-6">
                        @foreach($service->reviews as $review)
                        <div class="border-b pb-6 last:border-b-0 last:pb-0">
                            <div class="flex items-start gap-4">
                                <img src="{{ $review->reviewer->avatar ? Storage::url($review->reviewer->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($review->reviewer->name) }}" 
                                     alt="{{ $review->reviewer->name }}"
                                     class="w-12 h-12 rounded-full">
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-2">
                                        <div>
                                            <p class="font-bold text-gray-900">{{ $review->reviewer->name }}</p>
                                            <p class="text-sm text-gray-500">{{ $review->created_at->diffForHumans() }}</p>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="w-4 h-4 {{ $i <= round(($review->quality_rating + $review->communication_rating + $review->timeliness_rating) / 3) ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                            @endfor
                                        </div>
                                    </div>
                                    <p class="text-gray-700">{{ $review->comment }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- Sidebar (Prix et commande) --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-3xl p-6 shadow-lg sticky top-24">
                    <div class="mb-6">
                        <p class="text-gray-600 text-sm mb-2">À partir de</p>
                        <p class="text-5xl font-black text-blue-900 mb-1">
                            {{ number_format($service->price, 0) }}
                            <span class="text-2xl text-gray-600">FCFA</span>
                        </p>
                        <p class="text-sm text-gray-500">Livraison en {{ $service->delivery_time }} jour(s)</p>
                    </div>

                    @if(session('error'))
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
                        {{ session('error') }}
                    </div>
                    @endif

                    <button 
                        wire:click="orderService"
                        class="w-full bg-gradient-to-r from-blue-900 to-blue-800 hover:from-blue-800 hover:to-blue-700 text-white font-bold py-4 rounded-2xl text-lg transition transform hover:scale-105 shadow-xl mb-4"
                    >
                        Commander maintenant →
                    </button>

                    <button class="w-full border-2 border-blue-900 text-blue-900 hover:bg-blue-50 font-bold py-4 rounded-2xl transition">
                        Contacter le prestataire
                    </button>

                    <div class="mt-6 pt-6 border-t space-y-3 text-sm text-gray-600">
                        @foreach(['Paiement sécurisé', 'Satisfaction garantie', 'Support 24/7'] as $g)
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>{{ $g }}</span>
                        </div>
                        @endforeach
                    </div>

                    {{-- Signaler le service --}}
                    @auth
                    <div class="mt-4 pt-4 border-t text-center">
                        <a href="{{ route('reports.create', ['service' => $service->id]) }}"
                           class="text-xs text-gray-400 hover:text-red-500 transition inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6H13l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
                            Signaler ce service
                        </a>
                    </div>
                    @endauth
                </div>
            </div>
        </div>

        {{-- Services similaires --}}
        @if($similarServices->count() > 0)
        <div class="mt-16">
            <h2 class="text-3xl font-black mb-8">Services similaires</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($similarServices as $similar)
                <a href="{{ route('services.show', $similar) }}" 
                   class="bg-white rounded-3xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                    <div class="relative h-48 bg-gradient-to-br from-blue-100 to-blue-200">
                        @if($similar->cover_image)
                            <img src="{{ Storage::url($similar->cover_image) }}" 
                                 alt="{{ $similar->title }}"
                                 class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-white text-4xl font-black bg-gradient-to-br from-blue-500 to-blue-700">
                                {{ substr($similar->title, 0, 2) }}
                            </div>
                        @endif
                    </div>
                    <div class="p-5">
                        <h3 class="font-bold text-lg mb-2 line-clamp-2 min-h-[3.5rem]">{{ $similar->title }}</h3>
                        <div class="flex items-center gap-2 mb-3">
                            <img src="{{ $similar->prestataire->avatar ? Storage::url($similar->prestataire->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($similar->prestataire->name) }}" 
                                 alt="{{ $similar->prestataire->name }}"
                                 class="w-8 h-8 rounded-full">
                            <span class="text-sm text-gray-600">{{ $similar->prestataire->name }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                {{ number_format($similar->rating, 1) }}
                            </span>
                            <span class="font-black text-blue-900">{{ number_format($similar->price, 0) }} FCFA</span>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>