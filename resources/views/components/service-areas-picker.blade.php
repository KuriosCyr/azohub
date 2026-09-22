@props(['selected' => [], 'nationwide' => false])

{{-- Sélecteur de zones d'intervention : interrupteur « Tout le Bénin » ou communes cochées,
     groupées par département. Envoie serves_nationwide (0/1) et service_areas[]. --}}
<div x-data="{
        nationwide: @js((bool) $nationwide),
        selected: @js(array_values($selected)),
        search: '',
        fold(text) { return (text || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase(); },
        match(name) { return this.fold(name).includes(this.fold(this.search)); },
        deptVisible(villes) { return villes.some(v => this.match(v)); },
        deptAll(villes) { return villes.every(v => this.selected.includes(v)); },
        toggleDept(villes) {
            if (this.deptAll(villes)) { this.selected = this.selected.filter(v => !villes.includes(v)); }
            else { this.selected = [...new Set([...this.selected, ...villes])]; }
        },
    }" class="space-y-4">

    {{-- Tout le Bénin --}}
    <label class="flex cursor-pointer items-start gap-3 rounded-xl border-2 border-ink-200 p-4 transition hover:border-ink-300"
           :class="nationwide ? 'border-ink-900 bg-terracotta-50' : ''">
        <input type="hidden" name="serves_nationwide" value="0">
        <input type="checkbox" name="serves_nationwide" value="1" x-model="nationwide"
               class="mt-1 h-4 w-4 rounded border-ink-300 text-ink-900 focus:ring-terracotta-600">
        <span>
            <span class="block font-bold text-ink-900">Tout le Bénin</span>
            <span class="block text-sm text-ink-500">Pour un service réalisable partout ou à distance (design, rédaction, développement…).</span>
        </span>
    </label>

    {{-- Communes --}}
    <div :class="nationwide ? 'pointer-events-none opacity-40' : ''" :aria-disabled="nationwide">
        <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <input type="search" x-model="search" placeholder="Rechercher une commune…" :disabled="nationwide"
                   class="w-full rounded-xl border-2 border-ink-200 px-4 py-2 text-sm transition focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 sm:max-w-xs">
            <p class="text-sm font-semibold text-ink-700">
                <span x-text="selected.length"></span> commune(s) sélectionnée(s)
                <button type="button" x-show="selected.length" @click="selected = []" class="ml-2 font-bold text-terracotta-600 underline">Tout effacer</button>
            </p>
        </div>

        <div class="max-h-96 space-y-4 overflow-y-auto rounded-xl border border-ink-100 bg-cream-50 p-4">
            @foreach(config('communes', []) as $departement => $villes)
                <div x-show="deptVisible(@js($villes))">
                    <div class="mb-2 flex items-center justify-between">
                        <p class="text-xs font-bold uppercase tracking-widest text-ink-400">{{ $departement }}</p>
                        <button type="button" @click="toggleDept(@js($villes))"
                                class="text-xs font-bold text-terracotta-600 hover:underline"
                                x-text="deptAll(@js($villes)) ? 'Tout retirer' : 'Tout sélectionner'"></button>
                    </div>
                    <div class="grid grid-cols-2 gap-x-4 gap-y-1.5 sm:grid-cols-3">
                        @foreach($villes as $ville)
                            <label class="flex cursor-pointer items-center gap-2 text-sm text-ink-700" x-show="match(@js($ville))">
                                <input type="checkbox" name="service_areas[]" value="{{ $ville }}" x-model="selected" :disabled="nationwide"
                                       class="h-4 w-4 rounded border-ink-300 text-ink-900 focus:ring-terracotta-600">
                                {{ $ville }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
