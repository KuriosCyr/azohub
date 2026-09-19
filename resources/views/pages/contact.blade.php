<x-app-layout>
    <div class="min-h-screen bg-cream">
        {{-- Hero Section --}}
        <div class="bg-ink-900 text-cream-50 py-20">
            <div class="container mx-auto px-4 text-center">
                <h1 class="text-5xl md:text-6xl font-serif font-medium mb-6">
                    <x-app-icon name="envelope" class="w-10 h-10 inline-block align-middle" /> Contactez-nous
                </h1>
                <p class="text-xl md:text-2xl text-cream-50/80 max-w-3xl mx-auto">
                    Notre équipe est là pour répondre à toutes vos questions
                </p>
            </div>
        </div>

        <div class="container mx-auto px-4 py-12">
            <div class="max-w-6xl mx-auto">
                <div class="grid lg:grid-cols-3 gap-8">
                    {{-- Formulaire de contact --}}
                    <div class="lg:col-span-2">
                        <div class="bg-cream-50 rounded-xl p-8 border border-ink-100">
                            <h2 class="text-3xl font-serif font-medium text-ink-900 mb-6">Envoyez-nous un message</h2>

                            @if(session('success'))
                                <div class="bg-forest-600/10 border-l-4 border-forest-600 text-forest-700 p-4 rounded mb-6">
                                    <p class="font-bold">Message envoyé !</p>
                                    <p>Nous vous répondrons dans les plus brefs délais.</p>
                                </div>
                            @endif

                            <form action="{{ route('contact.send') }}" method="POST" class="space-y-6">
                                @csrf

                                {{-- Nom --}}
                                <div>
                                    <label class="block font-bold text-ink-900 mb-2">
                                        Nom complet <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        name="name"
                                        value="{{ old('name', Auth::user()->name ?? '') }}"
                                        required
                                        class="w-full px-4 py-3 border-2 border-ink-200 rounded-lg focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition"
                                        placeholder="Votre nom"
                                    >
                                    @error('name')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Email --}}
                                <div>
                                    <label class="block font-bold text-ink-900 mb-2">
                                        Email <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="email"
                                        name="email"
                                        value="{{ old('email', Auth::user()->email ?? '') }}"
                                        required
                                        class="w-full px-4 py-3 border-2 border-ink-200 rounded-lg focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition"
                                        placeholder="votre@email.com"
                                    >
                                    @error('email')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Sujet --}}
                                <div>
                                    <label class="block font-bold text-ink-900 mb-2">
                                        Sujet <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        name="subject"
                                        required
                                        class="w-full px-4 py-3 border-2 border-ink-200 rounded-lg focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition"
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
                                    <label class="block font-bold text-ink-900 mb-2">
                                        Message <span class="text-red-500">*</span>
                                    </label>
                                    <textarea
                                        name="message"
                                        rows="6"
                                        required
                                        class="w-full px-4 py-3 border-2 border-ink-200 rounded-lg focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition"
                                        placeholder="Décrivez votre demande en détail..."
                                    >{{ old('message') }}</textarea>
                                    @error('message')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Bouton --}}
                                <button
                                    type="submit"
                                    class="w-full bg-ink-900 hover:bg-ink-700 text-cream-50 font-bold px-8 py-4 rounded-lg transition shadow-md"
                                >
<x-app-icon name="upload" class="w-5 h-5 inline-block" /> Envoyer le message
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Informations de contact --}}
                    <div class="space-y-6">
                        {{-- Coordonnées --}}
                        <div class="bg-cream-50 rounded-xl p-6 border border-ink-100">
                            <h3 class="text-xl font-serif font-medium text-ink-900 mb-6">Nos coordonnées</h3>

                            <div class="space-y-4">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 bg-terracotta-50 rounded-full flex items-center justify-center flex-shrink-0">
                                        <x-app-icon name="envelope" class="w-6 h-6 text-terracotta-600" />
                                    </div>
                                    <div>
                                        <p class="font-bold text-ink-900">Email</p>
                                        <a href="mailto:{{ config('services.azohub.support_email') }}" class="text-terracotta-600 hover:text-terracotta-700">
                                            {{ config('services.azohub.support_email') }}
                                        </a>
                                    </div>
                                </div>

                                @if(config('services.azohub.support_phone'))
                                    <div class="flex items-start gap-4">
                                        <div class="w-12 h-12 bg-forest-600/10 rounded-full flex items-center justify-center flex-shrink-0">
                                            <x-app-icon name="phone" class="w-6 h-6 text-forest-700" />
                                        </div>
                                        <div>
                                            <p class="font-bold text-ink-900">Téléphone</p>
                                            <a href="tel:{{ config('services.azohub.support_phone') }}" class="text-terracotta-600 hover:text-terracotta-700">
                                                {{ config('services.azohub.support_phone') }}
                                            </a>
                                        </div>
                                    </div>
                                @endif

                                @if(config('services.azohub.support_whatsapp'))
                                    <div class="flex items-start gap-4">
                                        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0">
                                            <x-app-icon name="chat" class="w-6 h-6 text-purple-600" />
                                        </div>
                                        <div>
                                            <p class="font-bold text-ink-900">WhatsApp</p>
                                            <a href="https://wa.me/{{ config('services.azohub.support_whatsapp') }}" class="text-terracotta-600 hover:text-terracotta-700" target="_blank">
                                                {{ config('services.azohub.support_phone') ?? config('services.azohub.support_whatsapp') }}
                                            </a>
                                        </div>
                                    </div>
                                @endif

                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 bg-ochre-500/15 rounded-full flex items-center justify-center flex-shrink-0">
                                        <x-app-icon name="map-pin" class="w-6 h-6 text-ochre-600" />
                                    </div>
                                    <div>
                                        <p class="font-bold text-ink-900">Adresse</p>
                                        <p class="text-ink-500">Cotonou, Bénin</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Horaires --}}
                        <div class="bg-ink-900 rounded-xl p-6 text-cream-50">
                            <h3 class="text-xl font-serif font-medium mb-4"><x-app-icon name="calendar" class="w-6 h-6 inline-block" /> Horaires</h3>
                            <div class="space-y-2 text-cream-50/80">
                                <p><strong class="text-cream-50">Lundi - Vendredi:</strong> 8h - 18h</p>
                                <p><strong class="text-cream-50">Samedi:</strong> 9h - 14h</p>
                                <p><strong class="text-cream-50">Dimanche:</strong> Fermé</p>
                            </div>
                        </div>

                        {{-- Réseaux sociaux --}}
                        <div class="bg-cream-50 rounded-xl p-6 border border-ink-100">
                            <h3 class="text-xl font-serif font-medium text-ink-900 mb-4">Suivez-nous</h3>
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
                        <div class="bg-ochre-500/15 border-2 border-ochre-500 rounded-xl p-6 text-center">
                            <div class="mb-3"><x-app-icon name="question-circle" class="w-10 h-10 inline-block" /></div>
                            <p class="font-bold text-ink-900 mb-3">Consultez d'abord notre FAQ</p>
                            <a href="{{ route('faq') }}" class="inline-block bg-ink-900 text-cream-50 font-bold px-6 py-3 rounded-lg hover:bg-ink-700 transition">
                                Voir la FAQ
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>