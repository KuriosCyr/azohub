<x-app-layout>
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="container mx-auto px-4 max-w-3xl">
            {{-- Header --}}
            <div class="mb-8">
                <a href="{{ route('orders.show', $order) }}" 
                   class="text-blue-900 hover:text-blue-700 font-bold mb-4 inline-block">
                    ← Retour à la commande
                </a>
                <h1 class="text-4xl font-black text-gray-900 mb-2">
                    <x-app-icon name="star" class="w-8 h-8 inline-block" /> Laisser un avis
                </h1>
                <p class="text-gray-600">
                    Partagez votre expérience pour aider la communauté
                </p>
            </div>

            {{-- Info commande --}}
            <div class="bg-white rounded-3xl p-6 shadow-lg mb-8">
                <div class="flex items-center gap-4">
                    @php
                        $reviewee = Auth::id() === $order->client_id ? $order->prestataire : $order->client;
                    @endphp
                    
                    <img src="{{ $reviewee->avatar ? Storage::url($reviewee->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($reviewee->name) }}" 
                         alt="{{ $reviewee->name }}"
                         class="w-16 h-16 rounded-full border-4 border-blue-100">
                    
                    <div class="flex-1">
                        <p class="text-sm text-gray-500">Vous évaluez</p>
                        <p class="text-xl font-bold text-gray-900">{{ $reviewee->name }}</p>
                        <p class="text-sm text-gray-600">{{ $order->service->title }}</p>
                    </div>
                    
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Commande</p>
                        <p class="font-bold text-blue-900">#{{ $order->order_number }}</p>
                    </div>
                </div>
            </div>

            {{-- Formulaire --}}
            <form action="{{ route('reviews.store', $order) }}" method="POST" class="bg-white rounded-3xl p-8 shadow-lg space-y-8">
                @csrf

                {{-- Note globale --}}
                <div>
                    <label class="block text-lg font-bold text-gray-900 mb-4">
                        Note globale <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-4">
                        <div class="flex gap-2" x-data="{ rating: {{ old('rating', 0) }} }">
                            @for($i = 1; $i <= 5; $i++)
                                <button 
                                    type="button"
                                    @click="rating = {{ $i }}"
                                    class="text-5xl transition transform hover:scale-110"
                                    :class="rating >= {{ $i }} ? 'text-yellow-400' : 'text-gray-300'"
                                >
                                    <x-app-icon name="star" class="w-10 h-10" />
                                </button>
                                <input type="hidden" name="rating" :value="rating">
                            @endfor
                        </div>
                        <div class="text-sm text-gray-500">
                            <p class="font-semibold">Cliquez pour noter</p>
                        </div>
                    </div>
                    @error('rating')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Notes détaillées --}}
                <div class="grid md:grid-cols-3 gap-6">
                    {{-- Qualité --}}
                    <div>
                        <label class="block font-bold text-gray-900 mb-3">
                            Qualité du travail <span class="text-red-500">*</span>
                        </label>
                        <div class="space-y-2" x-data="{ quality: {{ old('quality_rating', 0) }} }">
                            @for($i = 1; $i <= 5; $i++)
                                <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-blue-50 transition">
                                    <input 
                                        type="radio" 
                                        name="quality_rating" 
                                        value="{{ $i }}"
                                        x-model="quality"
                                        class="w-4 h-4 text-blue-900 focus:ring-blue-900"
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
                        <label class="block font-bold text-gray-900 mb-3">
                            Communication <span class="text-red-500">*</span>
                        </label>
                        <div class="space-y-2" x-data="{ communication: {{ old('communication_rating', 0) }} }">
                            @for($i = 1; $i <= 5; $i++)
                                <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-blue-50 transition">
                                    <input 
                                        type="radio" 
                                        name="communication_rating" 
                                        value="{{ $i }}"
                                        x-model="communication"
                                        class="w-4 h-4 text-blue-900 focus:ring-blue-900"
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
                        <label class="block font-bold text-gray-900 mb-3">
                            Respect des délais <span class="text-red-500">*</span>
                        </label>
                        <div class="space-y-2" x-data="{ timeliness: {{ old('timeliness_rating', 0) }} }">
                            @for($i = 1; $i <= 5; $i++)
                                <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-blue-50 transition">
                                    <input 
                                        type="radio" 
                                        name="timeliness_rating" 
                                        value="{{ $i }}"
                                        x-model="timeliness"
                                        class="w-4 h-4 text-blue-900 focus:ring-blue-900"
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

                {{-- Commentaire --}}
                <div>
                    <label class="block text-lg font-bold text-gray-900 mb-3">
                        Votre commentaire <span class="text-gray-500 text-sm font-normal">(optionnel)</span>
                    </label>
                    <textarea 
                        name="comment" 
                        rows="6"
                        placeholder="Partagez votre expérience avec {{ $reviewee->name }}. Qu'avez-vous apprécié ? Que pourrait-il améliorer ?"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl focus:border-blue-900 focus:ring-4 focus:ring-blue-100 transition"
                    >{{ old('comment') }}</textarea>
                    <p class="text-sm text-gray-500 mt-2">
                        <x-app-icon name="lightbulb" class="w-4 h-4 inline-block align-text-bottom" /> Un avis détaillé aide les autres utilisateurs à faire leur choix
                    </p>
                    @error('comment')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Conseils --}}
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                    <p class="text-sm text-blue-900 font-bold mb-2">Conseils pour un bon avis :</p>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li><x-app-icon name="check" class="w-4 h-4 inline-block align-text-bottom" /> Soyez honnête et constructif</li>
                        <li><x-app-icon name="check" class="w-4 h-4 inline-block align-text-bottom" /> Mentionnez ce qui vous a plu et ce qui pourrait être amélioré</li>
                        <li><x-app-icon name="check" class="w-4 h-4 inline-block align-text-bottom" /> Restez respectueux même si vous n'êtes pas satisfait</li>
                        <li><x-app-icon name="check" class="w-4 h-4 inline-block align-text-bottom" /> Donnez des exemples concrets</li>
                    </ul>
                </div>

                {{-- Boutons --}}
                <div class="flex gap-4">
                    <a href="{{ route('orders.show', $order) }}" 
                       class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold px-8 py-4 rounded-2xl text-center transition">
                        Annuler
                    </a>
                    <button 
                        type="submit"
                        class="flex-1 bg-gradient-to-r from-yellow-400 to-yellow-500 hover:from-yellow-500 hover:to-yellow-600 text-blue-900 font-black px-8 py-4 rounded-2xl transition transform hover:scale-105 shadow-lg">
                        <x-app-icon name="star" class="w-5 h-5 inline-block align-text-bottom" /> Publier mon avis
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>