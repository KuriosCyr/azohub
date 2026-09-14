<div class="min-h-screen bg-gray-50">
    {{-- Hero Section --}}
    <div class="bg-gradient-to-r from-blue-900 to-blue-800 text-white py-20">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-5xl md:text-6xl font-black mb-6">
                    <x-app-icon name="question-circle" class="w-10 h-10 inline-block align-middle" /> Foire Aux Questions
                </h1>
                <p class="text-xl md:text-2xl text-blue-100 mb-8">
                    Trouvez rapidement des réponses à vos questions
                </p>

                {{-- Barre de recherche --}}
                <div class="relative max-w-2xl mx-auto">
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search"
                        placeholder="Rechercher une question..." 
                        class="w-full px-6 py-5 rounded-full text-gray-900 text-lg focus:ring-4 focus:ring-yellow-400 focus:outline-none shadow-2xl"
                    >
                    <svg class="absolute right-6 top-1/2 transform -translate-y-1/2 w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-12">
        <div class="max-w-6xl mx-auto">
            {{-- Filtres par catégorie --}}
            <div class="bg-white rounded-3xl p-6 shadow-lg mb-8">
                <div class="flex flex-wrap gap-3">
                    <button 
                        wire:click="setCategory('all')"
                        class="px-6 py-3 rounded-full font-bold transition transform hover:scale-105
                            {{ $selectedCategory === 'all' ? 'bg-blue-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Toutes
                    </button>
                    @foreach($categories as $key => $label)
                        <button 
                            wire:click="setCategory('{{ $key }}')"
                            class="px-6 py-3 rounded-full font-bold transition transform hover:scale-105
                                {{ $selectedCategory === $key ? 'bg-blue-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Résultats --}}
            @if($search)
                {{-- Mode recherche --}}
                <div class="bg-white rounded-3xl p-8 shadow-lg">
                    <h2 class="text-2xl font-black text-gray-900 mb-6">
                        Résultats pour "{{ $search }}" ({{ $faqs->count() }})
                    </h2>

                    @forelse($faqs as $faq)
                        <div class="mb-4 last:mb-0 border-b last:border-0 pb-4 last:pb-0">
                            <button 
                                wire:click="toggleFaq({{ $faq->id }})"
                                class="w-full flex items-center justify-between p-4 hover:bg-gray-50 rounded-2xl transition text-left">
                                <div class="flex-1">
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-bold rounded-full">
                                        {{ $faq->category_label }}
                                    </span>
                                    <h3 class="font-bold text-gray-900 mt-2 text-lg">{{ $faq->question }}</h3>
                                </div>
                                <svg class="w-6 h-6 text-gray-400 flex-shrink-0 ml-4 transition transform {{ $openFaqId === $faq->id ? 'rotate-180' : '' }}" 
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            @if($openFaqId === $faq->id)
                                <div class="px-4 pb-4 text-gray-700 leading-relaxed">
                                    {!! nl2br(e($faq->answer)) !!}
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <div class="mb-4"><x-app-icon name="search" class="w-12 h-12 inline-block" /></div>
                            <p class="text-xl text-gray-600 mb-2">Aucun résultat trouvé</p>
                            <p class="text-gray-500">Essayez avec d'autres mots-clés</p>
                        </div>
                    @endforelse
                </div>
            @else
                {{-- Mode navigation par catégories --}}
                @if($selectedCategory === 'all')
                    {{-- Afficher toutes les catégories --}}
                    @foreach($faqsByCategory as $categoryKey => $categoryData)
                        <div class="bg-white rounded-3xl p-8 shadow-lg mb-8">
                            <h2 class="text-3xl font-black text-gray-900 mb-6 flex items-center gap-3">
                                @if($categoryKey === 'general')
                                    <x-app-icon name="lightbulb" class="w-8 h-8 inline-block" />
                                @elseif($categoryKey === 'prestataire')
                                    <x-app-icon name="briefcase" class="w-8 h-8 inline-block" />
                                @elseif($categoryKey === 'client')
                                    <x-app-icon name="user" class="w-8 h-8 inline-block" />
                                @elseif($categoryKey === 'paiement')
                                    <x-app-icon name="card" class="w-8 h-8 inline-block" />
                                @elseif($categoryKey === 'securite')
                                    <x-app-icon name="lock" class="w-8 h-8 inline-block" />
                                @endif
                                {{ $categoryData['label'] }}
                            </h2>

                            <div class="space-y-4">
                                @foreach($categoryData['faqs'] as $faq)
                                    <div class="border-b last:border-0 pb-4 last:pb-0">
                                        <button 
                                            wire:click="toggleFaq({{ $faq->id }})"
                                            class="w-full flex items-center justify-between p-4 hover:bg-gray-50 rounded-2xl transition text-left">
                                            <h3 class="font-bold text-gray-900 text-lg flex-1">{{ $faq->question }}</h3>
                                            <svg class="w-6 h-6 text-gray-400 flex-shrink-0 ml-4 transition transform {{ $openFaqId === $faq->id ? 'rotate-180' : '' }}" 
                                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>

                                        @if($openFaqId === $faq->id)
                                            <div class="px-4 pb-4 text-gray-700 leading-relaxed">
                                                {!! nl2br(e($faq->answer)) !!}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @else
                    {{-- Afficher une catégorie spécifique --}}
                    <div class="bg-white rounded-3xl p-8 shadow-lg">
                        <h2 class="text-3xl font-black text-gray-900 mb-8">
                            {{ $categories[$selectedCategory] }}
                        </h2>

                        <div class="space-y-4">
                            @forelse($faqs as $faq)
                                <div class="border-b last:border-0 pb-4 last:pb-0">
                                    <button 
                                        wire:click="toggleFaq({{ $faq->id }})"
                                        class="w-full flex items-center justify-between p-4 hover:bg-gray-50 rounded-2xl transition text-left">
                                        <h3 class="font-bold text-gray-900 text-lg flex-1">{{ $faq->question }}</h3>
                                        <svg class="w-6 h-6 text-gray-400 flex-shrink-0 ml-4 transition transform {{ $openFaqId === $faq->id ? 'rotate-180' : '' }}" 
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>

                                    @if($openFaqId === $faq->id)
                                        <div class="px-4 pb-4 text-gray-700 leading-relaxed">
                                            {!! nl2br(e($faq->answer)) !!}
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-center py-12">
                                    <p class="text-gray-500">Aucune question dans cette catégorie</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endif
            @endif

            {{-- Contact Support --}}
            <div class="bg-gradient-to-r from-blue-900 to-blue-800 text-white rounded-3xl p-8 text-center mt-12">
                <h3 class="text-2xl font-black mb-4">Vous ne trouvez pas votre réponse ?</h3>
                <p class="text-blue-100 mb-6">Notre équipe de support est là pour vous aider</p>
                <a href="{{ route('contact') }}" 
                   class="inline-block bg-yellow-400 text-blue-900 font-black px-8 py-4 rounded-full hover:bg-yellow-300 transition transform hover:scale-105 shadow-xl">
<x-app-icon name="envelope" class="w-5 h-5 inline-block" /> Contacter le support
                </a>
            </div>
        </div>
    </div>
</div>