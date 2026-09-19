<x-app-layout>
    <div class="min-h-screen bg-cream py-8">
        <div class="container mx-auto px-4 max-w-3xl">
            {{-- Header --}}
            <div class="mb-8">
                <a href="{{ route('orders.show', $order) }}"
                   class="text-ink-900 hover:text-terracotta-700 font-bold mb-4 inline-block">
                    ← Retour à la commande
                </a>
                <h1 class="text-4xl font-serif font-medium text-ink-900 mb-2">
                    <x-app-icon name="star" class="w-8 h-8 inline-block" /> Laisser un avis
                </h1>
                <p class="text-ink-500">
                    Partagez votre expérience pour aider la communauté
                </p>
            </div>

            {{-- Info commande --}}
            <div class="bg-cream-50 rounded-xl p-6 border border-ink-100 mb-8">
                <div class="flex items-center gap-4">
                    @php
                        $reviewee = Auth::id() === $order->client_id ? $order->prestataire : $order->client;
                    @endphp

                    <img src="{{ $reviewee->avatar ? Storage::url($reviewee->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($reviewee->name) }}"
                         alt="{{ $reviewee->name }}"
                         class="w-16 h-16 rounded-full border-4 border-ink-100">

                    <div class="flex-1">
                        <p class="text-sm text-ink-400">Vous évaluez</p>
                        <p class="text-xl font-bold text-ink-900">{{ $reviewee->name }}</p>
                        <p class="text-sm text-ink-500">{{ $order->display_title }}</p>
                    </div>

                    <div class="text-right">
                        <p class="text-sm text-ink-400">Commande</p>
                        <p class="font-bold text-ink-900">#{{ $order->order_number }}</p>
                    </div>
                </div>
            </div>

            {{-- Formulaire --}}
            <form action="{{ route('reviews.store', $order) }}" method="POST" class="bg-cream-50 rounded-xl p-8 border border-ink-100 space-y-8">
                @csrf

                {{-- Note globale --}}
                <div>
                    <label class="block text-lg font-bold text-ink-900 mb-4">
                        Note globale <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-4">
                        <div class="flex gap-2" x-data="{ rating: {{ old('rating', 0) }} }">
                            @for($i = 1; $i <= 5; $i++)
                                <button
                                    type="button"
                                    @click="rating = {{ $i }}"
                                    class="text-5xl transition"
                                    :class="rating >= {{ $i }} ? 'text-ochre-500' : 'text-ink-200'"
                                >
                                    <x-app-icon name="star" class="w-10 h-10" />
                                </button>
                                <input type="hidden" name="rating" :value="rating">
                            @endfor
                        </div>
                        <div class="text-sm text-ink-400">
                            <p class="font-semibold">Cliquez pour noter</p>
                        </div>
                    </div>
                    @error('rating')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Notes détaillées --}}
                @if($isClient)
                    <div class="grid md:grid-cols-3 gap-6">
                        {{-- Qualité --}}
                        <div>
                            <label class="block font-bold text-ink-900 mb-3">
                                Qualité du travail <span class="text-red-500">*</span>
                            </label>
                            <div class="space-y-2" x-data="{ quality: {{ old('quality_rating', 0) }} }">
                                @for($i = 1; $i <= 5; $i++)
                                    <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-terracotta-50 transition">
                                        <input
                                            type="radio"
                                            name="quality_rating"
                                            value="{{ $i }}"
                                            x-model="quality"
                                            class="w-4 h-4 text-terracotta-600 focus:ring-terracotta-600"
                                            {{ old('quality_rating') == $i ? 'checked' : '' }}
                                        >
                                        <x-star-rating :rating="$i" class="w-4 h-4" />
                                    </label>
                                @endfor
                            </div>
                            @error('quality_rating')
                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Communication --}}
                        <div>
                            <label class="block font-bold text-ink-900 mb-3">
                                Communication <span class="text-red-500">*</span>
                            </label>
                            <div class="space-y-2" x-data="{ communication: {{ old('communication_rating', 0) }} }">
                                @for($i = 1; $i <= 5; $i++)
                                    <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-terracotta-50 transition">
                                        <input
                                            type="radio"
                                            name="communication_rating"
                                            value="{{ $i }}"
                                            x-model="communication"
                                            class="w-4 h-4 text-terracotta-600 focus:ring-terracotta-600"
                                            {{ old('communication_rating') == $i ? 'checked' : '' }}
                                        >
                                        <x-star-rating :rating="$i" class="w-4 h-4" />
                                    </label>
                                @endfor
                            </div>
                            @error('communication_rating')
                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Délais --}}
                        <div>
                            <label class="block font-bold text-ink-900 mb-3">
                                Respect des délais <span class="text-red-500">*</span>
                            </label>
                            <div class="space-y-2" x-data="{ timeliness: {{ old('timeliness_rating', 0) }} }">
                                @for($i = 1; $i <= 5; $i++)
                                    <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-terracotta-50 transition">
                                        <input
                                            type="radio"
                                            name="timeliness_rating"
                                            value="{{ $i }}"
                                            x-model="timeliness"
                                            class="w-4 h-4 text-terracotta-600 focus:ring-terracotta-600"
                                            {{ old('timeliness_rating') == $i ? 'checked' : '' }}
                                        >
                                        <x-star-rating :rating="$i" class="w-4 h-4" />
                                    </label>
                                @endfor
                            </div>
                            @error('timeliness_rating')
                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                @else
                    <div class="grid md:grid-cols-2 gap-6">
                        {{-- Clarté des consignes --}}
                        <div>
                            <label class="block font-bold text-ink-900 mb-3">
                                Clarté des consignes <span class="text-red-500">*</span>
                            </label>
                            <div class="space-y-2" x-data="{ clarity: {{ old('clarity_rating', 0) }} }">
                                @for($i = 1; $i <= 5; $i++)
                                    <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-terracotta-50 transition">
                                        <input
                                            type="radio"
                                            name="clarity_rating"
                                            value="{{ $i }}"
                                            x-model="clarity"
                                            class="w-4 h-4 text-terracotta-600 focus:ring-terracotta-600"
                                            {{ old('clarity_rating') == $i ? 'checked' : '' }}
                                        >
                                        <x-star-rating :rating="$i" class="w-4 h-4" />
                                    </label>
                                @endfor
                            </div>
                            @error('clarity_rating')
                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Réactivité --}}
                        <div>
                            <label class="block font-bold text-ink-900 mb-3">
                                Réactivité <span class="text-red-500">*</span>
                            </label>
                            <div class="space-y-2" x-data="{ responsiveness: {{ old('responsiveness_rating', 0) }} }">
                                @for($i = 1; $i <= 5; $i++)
                                    <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-terracotta-50 transition">
                                        <input
                                            type="radio"
                                            name="responsiveness_rating"
                                            value="{{ $i }}"
                                            x-model="responsiveness"
                                            class="w-4 h-4 text-terracotta-600 focus:ring-terracotta-600"
                                            {{ old('responsiveness_rating') == $i ? 'checked' : '' }}
                                        >
                                        <x-star-rating :rating="$i" class="w-4 h-4" />
                                    </label>
                                @endfor
                            </div>
                            @error('responsiveness_rating')
                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                @endif

                {{-- Commentaire --}}
                <div>
                    <label class="block text-lg font-bold text-ink-900 mb-3">
                        Votre commentaire <span class="text-ink-400 text-sm font-normal">(optionnel)</span>
                    </label>
                    <textarea
                        name="comment"
                        rows="6"
                        placeholder="Partagez votre expérience avec {{ $reviewee->name }}. Qu'avez-vous apprécié ? Que pourrait-il améliorer ?"
                        class="w-full px-4 py-3 border-2 border-ink-200 rounded-lg focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition"
                    >{{ old('comment') }}</textarea>
                    <p class="text-sm text-ink-400 mt-2">
                        <x-app-icon name="lightbulb" class="w-4 h-4 inline-block align-text-bottom" /> Un avis détaillé aide les autres utilisateurs à faire leur choix
                    </p>
                    @error('comment')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Conseils --}}
                <div class="bg-terracotta-50 border-l-4 border-terracotta-600 p-4 rounded">
                    <p class="text-sm text-ink-900 font-bold mb-2">Conseils pour un bon avis :</p>
                    <ul class="text-sm text-ink-700 space-y-1">
                        <li><x-app-icon name="check" class="w-4 h-4 inline-block align-text-bottom" /> Soyez honnête et constructif</li>
                        <li><x-app-icon name="check" class="w-4 h-4 inline-block align-text-bottom" /> Mentionnez ce qui vous a plu et ce qui pourrait être amélioré</li>
                        <li><x-app-icon name="check" class="w-4 h-4 inline-block align-text-bottom" /> Restez respectueux même si vous n'êtes pas satisfait</li>
                        <li><x-app-icon name="check" class="w-4 h-4 inline-block align-text-bottom" /> Donnez des exemples concrets</li>
                    </ul>
                </div>

                {{-- Boutons --}}
                <div class="flex gap-4">
                    <a href="{{ route('orders.show', $order) }}"
                       class="flex-1 bg-ink-100/30 hover:bg-ink-100/50 text-ink-700 font-bold px-8 py-4 rounded-lg text-center transition">
                        Annuler
                    </a>
                    <button
                        type="submit"
                        class="flex-1 bg-terracotta-600 hover:bg-terracotta-700 text-cream-50 font-bold px-8 py-4 rounded-lg transition shadow-md">
                        <x-app-icon name="star" class="w-5 h-5 inline-block align-text-bottom" /> Publier mon avis
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>