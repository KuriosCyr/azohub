<x-public-layout>
    <div class="min-h-screen bg-gray-50">
        {{-- Hero Section --}}
        <div class="bg-gradient-to-r from-blue-900 to-blue-800 text-white py-20">
            <div class="container mx-auto px-4 text-center">
                <h1 class="text-5xl md:text-6xl font-black mb-6">
                    📧 Contactez-nous
                </h1>
                <p class="text-xl md:text-2xl text-blue-100 max-w-3xl mx-auto">
                    Notre équipe est là pour répondre à toutes vos questions
                </p>
            </div>
        </div>

        <div class="container mx-auto px-4 py-12">
            <div class="max-w-6xl mx-auto">
                <div class="grid lg:grid-cols-3 gap-8">
                    {{-- Formulaire de contact --}}
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-3xl p-8 shadow-lg">
                            <h2 class="text-3xl font-black text-gray-900 mb-6">Envoyez-nous un message</h2>

                            @if(session('success'))
                                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded mb-6">
                                    <p class="font-bold">Message envoyé !</p>
                                    <p>Nous vous répondrons dans les plus brefs délais.</p>
                                </div>
                            @endif

                            <form action="{{ route('contact.send') }}" method="POST" class="space-y-6">
                                @csrf

                                {{-- Nom --}}
                                <div>
                                    <label class="block font-bold text-gray-900 mb-2">
                                        Nom complet <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        name="name" 
                                        value="{{ old('name', Auth::user()->name ?? '') }}"
                                        required
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl focus:border-blue-900 focus:ring-4 focus:ring-blue-100 transition"
                                        placeholder="Votre nom"
                                    >
                                    @error('name')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Email --}}
                                <div>
                                    <label class="block font-bold text-gray-900 mb-2">
                                        Email <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="email" 
                                        name="email" 
                                        value="{{ old('email', Auth::user()->email ?? '') }}"
                                        required
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl focus:border-blue-900 focus:ring-4 focus:ring-blue-100 transition"
                                        placeholder="votre@email.com"
                                    >
                                    @error('email')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Sujet --}}
                                <div>
                                    <label class="block font-bold text-gray-900 mb-2">
                                        Sujet <span class="text-red-500">*</span>
                                    </label>
                                    <select 
                                        name="subject" 
                                        required
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl focus:border-blue-900 focus:ring-4 focus:ring-blue-100 transition"
                                    >
                                        <option value="">Choisissez un sujet</option>
                                        <option value="general" {{ old('subject') === 'general' ? 'selected' : '' }}>Question générale</option>
                                        <option value="commande" {{ old('subject') === 'commande' ? 'selected' : '' }}>Problème avec une commande</option>
                                        <option value="paiement" {{ old('subject') === 'paiement' ? 'selected' : '' }}>Question sur un paiement</option>
                                        <option value="technique" {{ old('subject') === 'technique' ? 'selected' : '' }}>Problème technique</option>
                                        <option value="compte" {{ old('subject') === 'compte' ? 'selected' : '' }}>Gestion de compte</option>
                                        <option value="partenariat" {{ old('subject') === 'partenariat' ? 'selected' : '' }}>Partenariat</option>
                                        <option value="autre" {{ old('subject') === 'autre' ? 'selected' : '' }}>Autre</option>
                                    </select>
                                    @error('subject')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Message --}}
                                <div>
                                    <label class="block font-bold text-gray-900 mb-2">
                                        Message <span class="text-red-500">*</span>
                                    </label>
                                    <textarea 
                                        name="message" 
                                        rows="6"
                                        required
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl focus:border-blue-900 focus:ring-4 focus:ring-blue-100 transition"
                                        placeholder="Décrivez votre demande en détail..."
                                    >{{ old('message') }}</textarea>
                                    @error('message')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Bouton --}}
                                <button 
                                    type="submit"
                                    class="w-full bg-blue-900 hover:bg-blue-800 text-white font-black px-8 py-4 rounded-2xl transition transform hover:scale-105 shadow-lg"
                                >
                                    📤 Envoyer le message
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Informations de contact --}}
                    <div class="space-y-6">
                        {{-- Coordonnées --}}
                        <div class="bg-white rounded-3xl p-6 shadow-lg">
                            <h3 class="text-xl font-black text-gray-900 mb-6">Nos coordonnées</h3>
                            
                            <div class="space-y-4">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 text-2xl">
                                        📧
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900">Email</p>
                                        <a href="mailto:support@azohub.com" class="text-blue-600 hover:text-blue-800">
                                            support@azohub.com
                                        </a>
                                    </div>
                                </div>

                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0 text-2xl">
                                        📱
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900">Téléphone</p>
                                        <a href="tel:+22900000000" class="text-blue-600 hover:text-blue-800">
                                            +229 XX XX XX XX
                                        </a>
                                    </div>
                                </div>

                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0 text-2xl">
                                        💬
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900">WhatsApp</p>
                                        <a href="https://wa.me/22900000000" class="text-blue-600 hover:text-blue-800" target="_blank">
                                            +229 XX XX XX XX
                                        </a>
                                    </div>
                                </div>

                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center flex-shrink-0 text-2xl">
                                        📍
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900">Adresse</p>
                                        <p class="text-gray-600">Cotonou, Bénin</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Horaires --}}
                        <div class="bg-gradient-to-br from-blue-900 to-blue-800 rounded-3xl p-6 text-white">
                            <h3 class="text-xl font-black mb-4">⏰ Horaires</h3>
                            <div class="space-y-2 text-blue-100">
                                <p><strong class="text-white">Lundi - Vendredi:</strong> 8h - 18h</p>
                                <p><strong class="text-white">Samedi:</strong> 9h - 14h</p>
                                <p><strong class="text-white">Dimanche:</strong> Fermé</p>
                            </div>
                            <div class="mt-4 pt-4 border-t border-blue-700">
                                <p class="text-sm">Temps de réponse moyen: <strong class="text-yellow-400">2h</strong></p>
                            </div>
                        </div>

                        {{-- Réseaux sociaux --}}
                        <div class="bg-white rounded-3xl p-6 shadow-lg">
                            <h3 class="text-xl font-black text-gray-900 mb-4">Suivez-nous</h3>
                            <div class="grid grid-cols-2 gap-3">
                                <a href="#" class="flex items-center justify-center gap-2 bg-blue-600 text-white p-3 rounded-xl hover:bg-blue-700 transition font-bold">
                                    Facebook
                                </a>
                                <a href="#" class="flex items-center justify-center gap-2 bg-sky-500 text-white p-3 rounded-xl hover:bg-sky-600 transition font-bold">
                                    Twitter
                                </a>
                                <a href="#" class="flex items-center justify-center gap-2 bg-pink-600 text-white p-3 rounded-xl hover:bg-pink-700 transition font-bold">
                                    Instagram
                                </a>
                                <a href="#" class="flex items-center justify-center gap-2 bg-blue-700 text-white p-3 rounded-xl hover:bg-blue-800 transition font-bold">
                                    LinkedIn
                                </a>
                            </div>
                        </div>

                        {{-- FAQ Link --}}
                        <div class="bg-yellow-100 border-2 border-yellow-400 rounded-3xl p-6 text-center">
                            <div class="text-4xl mb-3">❓</div>
                            <p class="font-bold text-gray-900 mb-3">Consultez d'abord notre FAQ</p>
                            <a href="{{ route('faq') }}" class="inline-block bg-blue-900 text-white font-bold px-6 py-3 rounded-full hover:bg-blue-800 transition">
                                Voir la FAQ
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-public-layout>