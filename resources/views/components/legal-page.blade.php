@props(['title', 'subtitle', 'updated', 'otherLabel', 'otherRoute'])

<x-app-layout>
    <div class="bg-cream-100">
        {{-- En-tête --}}
        <div class="bg-gradient-to-br from-ink-900 via-ink-900 to-ink-700 text-cream-50">
            <div class="container mx-auto px-4 py-14 md:py-20">
                <div class="max-w-6xl mx-auto">
                    <div class="inline-flex items-center gap-2 mb-6">
                        <div class="w-7 h-0.5 bg-clay-500"></div>
                        <span class="text-xs font-semibold tracking-widest uppercase text-clay-500">Informations légales</span>
                    </div>
                    <h1 class="font-serif text-4xl md:text-5xl font-medium leading-tight mb-4">{{ $title }}</h1>
                    <p class="text-lg text-cream-100/75 max-w-2xl leading-relaxed">{{ $subtitle }}</p>

                    <div class="mt-8 flex flex-wrap items-center gap-3 text-sm">
                        <span class="inline-flex items-center rounded-full bg-cream-50/10 px-4 py-1.5 text-cream-100/80">
                            Dernière mise à jour : {{ $updated }}
                        </span>
                        <a href="{{ $otherRoute }}" class="inline-flex items-center rounded-full bg-cream-50/10 hover:bg-cream-50/20 px-4 py-1.5 font-semibold text-cream-50 transition">
                            {{ $otherLabel }} &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Corps : sommaire (desktop) + sections --}}
        <div class="container mx-auto px-4 py-10 md:py-14">
            <div class="max-w-6xl mx-auto lg:grid lg:grid-cols-[250px_minmax(0,1fr)] lg:gap-12"
                 x-data="{
                    items: [],
                    active: '',
                    init() {
                        const sections = [...this.$el.querySelectorAll('[data-legal-section]')];
                        this.items = sections.map(s => ({ id: s.id, label: s.dataset.title }));
                        const observer = new IntersectionObserver(entries => {
                            entries.forEach(e => { if (e.isIntersecting) this.active = e.target.id; });
                        }, { rootMargin: '-15% 0px -75% 0px' });
                        sections.forEach(s => observer.observe(s));
                    }
                 }">
                <aside class="hidden lg:block">
                    <nav class="sticky top-24 max-h-[calc(100vh-7rem)] overflow-y-auto pr-2" aria-label="Sommaire">
                        <p class="text-xs font-semibold uppercase tracking-widest text-ink-400 mb-3">Sommaire</p>
                        <ul class="space-y-0.5 border-l border-ink-200">
                            <template x-for="item in items" :key="item.id">
                                <li>
                                    <a :href="'#' + item.id"
                                       x-text="item.label"
                                       :class="active === item.id
                                            ? 'border-terracotta-600 text-terracotta-700 font-semibold'
                                            : 'border-transparent text-ink-500 hover:text-ink-900'"
                                       class="block -ml-px border-l-2 py-1.5 pl-4 text-sm leading-snug transition"></a>
                                </li>
                            </template>
                        </ul>
                    </nav>
                </aside>

                <div class="min-w-0 space-y-5">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
