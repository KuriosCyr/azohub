<div class="min-h-screen bg-cream">
    {{-- Hero Section --}}
    <div class="bg-ink-900 text-cream-50 py-20">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-5xl md:text-6xl font-serif font-medium mb-6">
                    <x-app-icon name="question-circle" class="w-10 h-10 inline-block align-middle" /> Foire Aux Questions
                </h1>
                <p class="text-xl md:text-2xl text-cream-50/80 mb-8">
                    Trouvez rapidement des réponses à vos questions
                </p>

                {{-- Barre de recherche --}}
                <div class="relative max-w-2xl mx-auto">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Rechercher une question..."
                        class="w-full px-6 py-5 rounded-full text-ink-900 text-lg focus:ring-4 focus:ring-terracotta-600 focus:outline-none shadow-lg"
                    >
                    <svg class="absolute right-6 top-1/2 transform -translate-y-1/2 w-6 h-6 text-ink-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-12">
        <div class="max-w-6xl mx-auto">
            {{-- Filtres par catégorie --}}
            <div class="bg-cream-50 rounded-xl p-6 border border-ink-100 mb-8">
                <div class="flex flex-wrap gap-3">
                    <button
                        wire:click="setCategory('all')"
                        class="px-6 py-3 rounded-full font-bold transition
                            {{ $selectedCategory === 'all' ? 'bg-ink-900 text-cream-50' : 'bg-ink-100/30 text-ink-700 hover:bg-ink-100/50' }}">
                        Toutes
                    </button>
                    @foreach($categories as $key => $label)
                        <button
                            wire:click="setCategory('{{ $key }}')"
                            class="px-6 py-3 rounded-full font-bold transition
                                {{ $selectedCategory === $key ? 'bg-ink-900 text-cream-50' : 'bg-ink-100/30 text-ink-700 hover:bg-ink-100/50' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Résultats --}}
            @if($search)
                {{-- Mode recherche --}}
                <div class="bg-cream-50 rounded-xl p-8 border border-ink-100">
                    <h2 class="text-2xl font-serif font-medium text-ink-900 mb-6">
                        Résultats pour "{{ $search }}" ({{ $faqs->count() }})
                    </h2>

                    @forelse($faqs as $faq)
                        {{-- Dépliage géré entièrement côté client (Alpine) : avant, chaque clic
                             appelait toggleFaq() côté serveur (propriété Livewire $openFaqId),
                             un aller-retour réseau pour un simple show/hide qui rendait le
                             dépliement perceptiblement lent (BUG043 QA). --}}
                        <div x-data="{ open: {{ $openFaqId === $faq->id ? 'true' : 'false' }} }" class="mb-4 last:mb-0 border-b last:border-0 pb-4 last:pb-0">
                            <button
                                @click="open = !open"
                                class="w-full flex items-center justify-between p-4 hover:bg-ink-100/30 rounded-lg transition text-left">
                                <div class="flex-1">
                                    <span class="px-3 py-1 bg-terracotta-50 text-terracotta-700 text-xs font-bold rounded-full">
                                        {{ $faq->category_label }}
                                    </span>
                                    <h3 class="font-bold text-ink-900 mt-2 text-lg">{{ $faq->question }}</h3>
                                </div>
                                <svg class="w-6 h-6 text-ink-300 flex-shrink-0 ml-4 transition transform" :class="open ? 'rotate-180' : ''"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <div x-show="open" x-transition>
                                <div class="px-4 pb-4 text-ink-700 leading-relaxed">
                                    {!! nl2br(e($faq->answer)) !!}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <div class="mb-4"><x-app-icon name="search" class="w-12 h-12 inline-block" /></div>
                            <p class="text-xl text-ink-500 mb-2">Aucun résultat trouvé</p>
                            <p class="text-ink-400">Essayez avec d'autres mots-clés</p>
                        </div>
                    @endforelse
                </div>
            @else
                {{-- Mode navigation par catégories --}}
                @if($selectedCategory === 'all')
                    {{-- Afficher toutes les catégories --}}
                    @foreach($faqsByCategory as $categoryKey => $categoryData)
                        <div class="bg-cream-50 rounded-xl p-8 border border-ink-100 mb-8">
                            <h2 class="text-3xl font-serif font-medium text-ink-900 mb-6 flex items-center gap-3">
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
                                    <div x-data="{ open: {{ $openFaqId === $faq->id ? 'true' : 'false' }} }" class="border-b last:border-0 pb-4 last:pb-0">
                                        <button
                                            @click="open = !open"
                                            class="w-full flex items-center justify-between p-4 hover:bg-ink-100/30 rounded-lg transition text-left">
                                            <h3 class="font-bold text-ink-900 text-lg flex-1">{{ $faq->question }}</h3>
                                            <svg class="w-6 h-6 text-ink-300 flex-shrink-0 ml-4 transition transform" :class="open ? 'rotate-180' : ''"
                                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>

                                        <div x-show="open" x-transition>
                                            <div class="px-4 pb-4 text-ink-700 leading-relaxed">
                                                {!! nl2br(e($faq->answer)) !!}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @else
                    {{-- Afficher une catégorie spécifique --}}
                    <div class="bg-cream-50 rounded-xl p-8 border border-ink-100">
                        <h2 class="text-3xl font-serif font-medium text-ink-900 mb-8">
                            {{ $categories[$selectedCategory] }}
                        </h2>

                        <div class="space-y-4">
                            @forelse($faqs as $faq)
                                <div x-data="{ open: {{ $openFaqId === $faq->id ? 'true' : 'false' }} }" class="border-b last:border-0 pb-4 last:pb-0">
                                    <button
                                        @click="open = !open"
                                        class="w-full flex items-center justify-between p-4 hover:bg-ink-100/30 rounded-lg transition text-left">
                                        <h3 class="font-bold text-ink-900 text-lg flex-1">{{ $faq->question }}</h3>
                                        <svg class="w-6 h-6 text-ink-300 flex-shrink-0 ml-4 transition transform" :class="open ? 'rotate-180' : ''"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>

                                    <div x-show="open" x-transition>
                                        <div class="px-4 pb-4 text-ink-700 leading-relaxed">
                                            {!! nl2br(e($faq->answer)) !!}
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-12">
                                    <p class="text-ink-400">Aucune question dans cette catégorie</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endif
            @endif

            {{-- Contact Support --}}
            <div class="bg-ink-900 text-cream-50 rounded-xl p-8 text-center mt-12">
                <h3 class="text-2xl font-serif font-medium mb-4">Vous ne trouvez pas votre réponse ?</h3>
                <p class="text-cream-50/80 mb-6">Notre équipe de support est là pour vous aider</p>
                <a href="{{ route('contact') }}"
                   class="inline-block bg-terracotta-600 text-cream-50 font-bold px-8 py-4 rounded-lg hover:bg-terracotta-700 transition shadow-md">
<x-app-icon name="envelope" class="w-5 h-5 inline-block" /> Contacter le support
                </a>
            </div>
        </div>
    </div>
</div>