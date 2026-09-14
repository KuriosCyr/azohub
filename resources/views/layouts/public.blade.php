<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Azohub') }} - Plateforme de services au Bénin</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm">
        <div class="container mx-auto px-4">
            <div class="flex justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center">
                        <span class="text-3xl font-black text-blue-900">Azo</span>
                        <span class="text-3xl font-black text-yellow-400">hub</span>
                        <span class="ml-2 text-xl">🇧🇯</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('home') }}" class="text-gray-700 hover:text-blue-600 font-semibold transition">
                        Accueil
                    </a>
                    
                    <!-- Dropdown Catégories -->
                    <div x-data="{ open: false }" @click.away="open = false" class="relative">
                        <button @click="open = !open" class="text-gray-700 hover:text-blue-600 font-semibold flex items-center gap-1 transition">
                            Catégories
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <div x-show="open" 
                             x-transition
                             class="absolute top-full left-0 mt-2 w-64 bg-white rounded-2xl shadow-2xl py-2 z-50 border border-gray-100">
                            @php
                                $categories = \App\Models\Category::active()->take(10)->get();
                            @endphp
                            @foreach($categories as $category)
                                <a href="{{ route('services.index', ['category' => $category->slug]) }}" 
                                   class="flex items-center gap-3 px-4 py-3 hover:bg-blue-50 transition">
                                    <span class="text-2xl">
                                        @php
                                            $icons = [
                                                'Hammer' => '🔨', 'Code' => '💻', 'Home' => '🏠',
                                                'GraduationCap' => '🎓', 'Calendar' => '📅', 'Truck' => '🚚',
                                                'Sparkles' => '✨', 'Wrench' => '🔧', 'FileText' => '📄', 'Heart' => '❤️',
                                            ];
                                            echo $icons[$category->icon] ?? '📦';
                                        @endphp
                                    </span>
                                    <span class="font-semibold text-gray-700 hover:text-blue-600">{{ $category->name }}</span>
                                </a>
                            @endforeach
                            <div class="border-t border-gray-100 mt-2 pt-2">
                                <a href="{{ route('services.index') }}" class="block px-4 py-3 text-blue-600 font-bold hover:bg-blue-50 transition">
                                    Voir toutes les catégories →
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <a href="{{ route('services.index') }}" class="text-gray-700 hover:text-blue-600 font-semibold transition">
                        Services
                    </a>
                    <a href="#" class="text-gray-700 hover:text-blue-600 font-semibold transition">
                        Comment ça marche
                    </a>
                </div>

                <!-- Auth Links -->
                <div class="flex items-center space-x-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-blue-600 font-semibold">
                            Dashboard
                        </a>
                        <div class="flex items-center space-x-2">
                            <img src="{{ Auth::user()->avatar ? Storage::url(Auth::user()->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) }}" 
                                 alt="{{ Auth::user()->name }}"
                                 class="w-8 h-8 rounded-full">
                            <span class="text-sm font-semibold text-gray-700">{{ Auth::user()->name }}</span>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-blue-600 font-semibold">
                            Connexion
                        </a>
                        <a href="{{ route('register') }}" class="bg-blue-900 hover:bg-blue-800 text-white font-bold px-6 py-2 rounded-full transition">
                            S'inscrire
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Content -->
    <main>
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- About -->
                <div>
                    <h3 class="text-xl font-black mb-4">
                        <span class="text-white">Azo</span><span class="text-yellow-400">hub</span> 🇧🇯
                    </h3>
                    <p class="text-gray-400">
                        La plateforme n°1 pour trouver des prestataires de confiance au Bénin.
                    </p>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="font-bold mb-4">Liens rapides</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="{{ route('home') }}" class="hover:text-white transition">Accueil</a></li>
                        <li><a href="{{ route('services.index') }}" class="hover:text-white transition">Services</a></li>
                        <li><a href="#" class="hover:text-white transition">Comment ça marche</a></li>
                        <li><a href="#" class="hover:text-white transition">Blog</a></li>
                    </ul>
                </div>

                <!-- For Professionals -->
                <div>
                    <h4 class="font-bold mb-4">Professionnels</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="{{ route('register') }}" class="hover:text-white transition">Devenir prestataire</a></li>
                        <li><a href="#" class="hover:text-white transition">Tarifs</a></li>
                        <li><a href="#" class="hover:text-white transition">Guide du prestataire</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div>
                    <h4 class="font-bold mb-4">Contact</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li>📧 contact@azohub.bj</li>
                        <li>📱 +229 XX XX XX XX</li>
                        <li>📍 Cotonou, Bénin</li>
                    </ul>
                    <div class="mt-4 flex space-x-3">
                        <a href="#" class="text-gray-400 hover:text-white text-xl transition">📘</a>
                        <a href="#" class="text-gray-400 hover:text-white text-xl transition">📷</a>
                        <a href="#" class="text-gray-400 hover:text-white text-xl transition">🐦</a>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; {{ date('Y') }} Azohub. Tous droits réservés.</p>
                <div class="mt-2 space-x-4">
                    <a href="#" class="hover:text-white transition">Conditions d'utilisation</a>
                    <a href="#" class="hover:text-white transition">Politique de confidentialité</a>
                    <a href="#" class="hover:text-white transition">CGU</a>
                </div>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>