<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Azohub') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    <!-- Scripts (Alpine.js est déjà bundlé et démarré dans resources/js/app.js) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-gray-50">
    <!-- Navigation (IDENTIQUE AU PUBLIC) -->
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center gap-1">
                        <span class="text-2xl font-black text-blue-900">Azo</span><span class="text-2xl font-black text-yellow-400">hub</span>
                        <span class="ml-1 inline-flex items-center justify-center w-5 h-3 rounded-sm overflow-hidden">
                            <svg viewBox="0 0 45 30" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                                <rect width="15" height="30" fill="#008751"/>
                                <rect x="15" width="15" height="30" fill="#FCD116"/>
                                <rect x="30" width="15" height="30" fill="#E8112D"/>
                            </svg>
                        </span>
                    </a>
                </div>

                <!-- Navigation principale (desktop) -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('home') }}" class="text-gray-700 hover:text-blue-900 font-semibold transition {{ request()->routeIs('home') ? 'text-blue-900 border-b-2 border-blue-900' : '' }}">
                        Accueil
                    </a>
                    
                    <a href="{{ route('services.index') }}" class="text-gray-700 hover:text-blue-900 font-semibold transition {{ request()->routeIs('services.*') && !request()->routeIs('prestataire.services.*') ? 'text-blue-900 border-b-2 border-blue-900' : '' }}">
                        Services
                    </a>

                    <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-blue-900 font-semibold transition {{ request()->routeIs('*.dashboard') ? 'text-blue-900 border-b-2 border-blue-900' : '' }}">
                        Dashboard
                    </a>

                    @if(Auth::user()->isPrestataire())
                        <a href="{{ route('prestataire.services.index') }}" class="text-gray-700 hover:text-blue-900 font-semibold transition {{ request()->routeIs('prestataire.services.*') ? 'text-blue-900 border-b-2 border-blue-900' : '' }}">
                            Mes services
                        </a>
                    @endif

                    <a href="#" class="text-gray-700 hover:text-blue-900 font-semibold transition">
                        Comment ça marche
                    </a>
                </div>

                <!-- User Menu -->
                <div class="hidden md:flex items-center space-x-4">
                    <div x-data="{ open: false }" @click.away="open = false" class="relative">
                        <button @click="open = !open" class="flex items-center gap-3 hover:bg-gray-50 rounded-2xl px-4 py-2 transition">
                            <img src="{{ Auth::user()->avatar ? Storage::url(Auth::user()->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) }}" 
                                 alt="{{ Auth::user()->name }}"
                                 class="w-10 h-10 rounded-full border-2 border-blue-100">
                            <div class="text-left">
                                <p class="text-sm font-bold text-gray-900">{{ Str::limit(Auth::user()->name, 20) }}</p>
                                <p class="text-xs text-gray-500">{{ ucfirst(Auth::user()->role) }}</p>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div x-show="open" 
                             x-transition
                             class="absolute right-0 top-full mt-2 w-64 bg-white rounded-2xl shadow-2xl py-2 z-50 border border-gray-100"
                             style="display: none;">
                            
                            <!-- User Info -->
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="font-bold text-gray-900">{{ Auth::user()->name }}</p>
                                <p class="text-sm text-gray-500">{{ Auth::user()->email }}</p>
                                
                                @if(Auth::user()->isPrestataire())
                                    <div class="mt-2 flex items-center gap-2">
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-bold rounded-full">
                                            {{ ucfirst(Auth::user()->level) }}
                                        </span>
                                        @if(Auth::user()->identity_verified)
                                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-bold rounded-full">
                                                Vérifié
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <!-- Menu Links -->
                            <div class="py-2">
                                @if(Auth::user()->isPrestataire())
                                    <a href="{{ route('prestataire.dashboard') }}" class="block px-4 py-3 hover:bg-blue-50 transition text-gray-700 font-semibold">
                                        Dashboard
                                    </a>
                                    <a href="{{ route('prestataire.profile', Auth::user()->id) }}" class="block px-4 py-3 hover:bg-blue-50 transition text-gray-700 font-semibold">
                                        Mon profil public
                                    </a>
                                    <a href="{{ route('prestataire.services.index') }}" class="block px-4 py-3 hover:bg-blue-50 transition text-gray-700 font-semibold">
                                        Mes services
                                    </a>
                                    <a href="#" class="block px-4 py-3 hover:bg-blue-50 transition text-gray-700 font-semibold">
                                        Portefeuille
                                        <span class="text-xs text-gray-500 ml-2">{{ number_format(Auth::user()->wallet_balance ?? 0, 0) }} FCFA</span>
                                    </a>
                                @else
                                    <a href="{{ route('client.dashboard') }}" class="block px-4 py-3 hover:bg-blue-50 transition text-gray-700 font-semibold">
                                        Dashboard
                                    </a>
                                    <a href="{{ route('client.dashboard') }}" class="block px-4 py-3 hover:bg-blue-50 transition text-gray-700 font-semibold">
                                        Mes commandes
                                    </a>
                                @endif
                            </div>

                            <div class="border-t border-gray-100 py-2">
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-3 hover:bg-gray-50 transition text-gray-700 font-semibold">
                                    Paramètres
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left block px-4 py-3 hover:bg-red-50 transition text-red-600 font-semibold">
                                        Déconnexion
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button x-data @click="$dispatch('toggle-mobile-menu')" class="text-gray-700 hover:text-blue-900 p-2 rounded-lg hover:bg-gray-100 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Menu Drawer -->
    <div
        x-data="{ open: false }"
        @toggle-mobile-menu.window="open = !open"
        @keydown.escape.window="open = false"
        class="md:hidden"
    >
        <div x-show="open"
             x-transition:enter="transition-opacity ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="open = false"
             class="fixed inset-0 bg-black/50 z-40"
             style="display:none;"></div>

        <div x-show="open"
             x-transition:enter="transition ease-out duration-200 transform"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-150 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             class="fixed top-0 left-0 h-full w-72 bg-white z-50 shadow-2xl flex flex-col overflow-y-auto"
             style="display:none;">

            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <a href="{{ route('home') }}" class="flex items-center gap-1">
                    <span class="text-xl font-black text-blue-900">Azo</span><span class="text-xl font-black text-yellow-400">hub</span>
                </a>
                <button @click="open = false" class="p-2 rounded-lg hover:bg-gray-100 transition text-gray-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <nav class="flex-1 px-4 py-6 space-y-1">
                <a href="{{ route('home') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-700 hover:bg-blue-50 hover:text-blue-900 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Accueil
                </a>
                <a href="{{ route('services.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-700 hover:bg-blue-50 hover:text-blue-900 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    Services
                </a>
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-700 hover:bg-blue-50 hover:text-blue-900 transition {{ request()->routeIs('*.dashboard') ? 'bg-blue-50 text-blue-900' : '' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/></svg>
                    Dashboard
                </a>
                @if(Auth::user()->isPrestataire())
                    <a href="{{ route('prestataire.services.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-700 hover:bg-blue-50 hover:text-blue-900 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        Mes services
                    </a>
                @endif
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-700 hover:bg-blue-50 hover:text-blue-900 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Paramètres
                </a>
            </nav>

            <div class="px-4 py-4 border-t border-gray-100">
                <div class="flex items-center gap-3 mb-4">
                    <img src="{{ Auth::user()->avatar ? Storage::url(Auth::user()->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) }}" alt="{{ Auth::user()->name }}" class="w-10 h-10 rounded-full border-2 border-blue-100">
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-gray-900 truncate">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-gray-500">{{ ucfirst(Auth::user()->role) }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-red-50 hover:bg-red-100 text-red-600 font-semibold rounded-xl transition text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Déconnexion
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <div class="container mx-auto px-4 py-4">
        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-4 rounded">
                <p class="text-green-700 font-semibold">{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4 rounded">
                <p class="text-red-700 font-semibold">{{ session('error') }}</p>
            </div>
        @endif

        @if(session('info'))
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-4 rounded">
                <p class="text-blue-700 font-semibold">{{ session('info') }}</p>
            </div>
        @endif
    </div>

    <!-- Page Content -->
    <main>
        {{ $slot }}
    </main>

    @livewireScripts
    @stack('scripts')
</body>
</html>