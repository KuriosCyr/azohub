<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Azohub') }}</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,wght@0,400;0,500;0,600;1,500&family=Work+Sans:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@800&display=swap" rel="stylesheet" />

    <!-- Scripts (Alpine.js est déjà bundlé et démarré dans resources/js/app.js) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <noscript><style>.reveal{opacity:1!important;transform:none!important;}</style></noscript>
</head>
<body class="font-sans antialiased bg-cream text-ink-900">
    <!-- Navigation (IDENTIQUE AU PUBLIC) -->
    <nav class="bg-cream-50 border-b border-ink-100 sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center">
                        <x-logo-lockup class="h-10 w-auto" />
                    </a>
                </div>

                <!-- Navigation principale (desktop) -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('home') }}" class="text-sm font-medium text-ink-500 hover:text-ink-900 transition {{ request()->routeIs('home') ? 'text-ink-900 border-b-2 border-terracotta-600 pb-1' : '' }}">
                        Accueil
                    </a>

                    <a href="{{ route('services.index') }}" class="text-sm font-medium text-ink-500 hover:text-ink-900 transition {{ request()->routeIs('services.*') && !request()->routeIs('prestataire.services.*') ? 'text-ink-900 border-b-2 border-terracotta-600 pb-1' : '' }}">
                        Services
                    </a>

                    <a href="{{ route('dashboard') }}" class="text-sm font-medium text-ink-500 hover:text-ink-900 transition {{ request()->routeIs('*.dashboard') ? 'text-ink-900 border-b-2 border-terracotta-600 pb-1' : '' }}">
                        Dashboard
                    </a>

                    @if(Auth::user()->isPrestataire())
                        <a href="{{ route('prestataire.services.index') }}" class="text-sm font-medium text-ink-500 hover:text-ink-900 transition {{ request()->routeIs('prestataire.services.*') ? 'text-ink-900 border-b-2 border-terracotta-600 pb-1' : '' }}">
                            Mes services
                        </a>

                        <a href="{{ route('service-requests.index') }}" class="text-sm font-medium text-ink-500 hover:text-ink-900 transition {{ request()->routeIs('service-requests.*') ? 'text-ink-900 border-b-2 border-terracotta-600 pb-1' : '' }}">
                            Opportunités
                        </a>
                    @else
                        <a href="{{ route('service-requests.index', ['mine' => 1]) }}" class="text-sm font-medium text-ink-500 hover:text-ink-900 transition {{ request()->routeIs('service-requests.*') ? 'text-ink-900 border-b-2 border-terracotta-600 pb-1' : '' }}">
                            Mes demandes
                        </a>
                    @endif

                    <a href="{{ route('conversations.index') }}" class="text-sm font-medium text-ink-500 hover:text-ink-900 transition {{ request()->routeIs('conversations.*') ? 'text-ink-900 border-b-2 border-terracotta-600 pb-1' : '' }}">
                        Messages
                    </a>

                    <a href="{{ route('how-it-works') }}" class="text-sm font-medium text-ink-500 hover:text-ink-900 transition">
                        Comment ça marche
                    </a>
                </div>

                <!-- User Menu -->
                <div class="hidden md:flex items-center space-x-4">
                    <livewire:notification-bell />

                    <div x-data="{ open: false }" @click.away="open = false" class="relative">
                        <button @click="open = !open" class="flex items-center gap-3 hover:bg-ink-100/40 rounded-lg px-3 py-2 transition">
                            <img src="{{ Auth::user()->avatar ? Storage::url(Auth::user()->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) }}"
                                 alt="{{ Auth::user()->name }}"
                                 class="w-9 h-9 rounded-full border border-ink-200">
                            <div class="text-left">
                                <p class="text-sm font-semibold text-ink-900">{{ Str::limit(Auth::user()->name, 20) }}</p>
                                <p class="text-xs text-ink-400">{{ ucfirst(Auth::user()->role) }}</p>
                            </div>
                            <svg class="w-4 h-4 text-ink-300 transition-transform" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="open"
                             x-transition
                             class="absolute right-0 top-full mt-2 w-64 bg-cream-50 rounded-lg shadow-lg py-2 z-50 border border-ink-100"
                             style="display: none;">

                            <!-- User Info -->
                            <div class="px-4 py-3 border-b border-ink-100">
                                <p class="font-semibold text-ink-900">{{ Auth::user()->name }}</p>
                                <p class="text-sm text-ink-400">{{ Auth::user()->email }}</p>

                                @if(Auth::user()->isPrestataire())
                                    <div class="mt-2 flex items-center gap-2">
                                        <span class="px-2 py-1 bg-terracotta-50 text-terracotta-700 text-xs font-semibold rounded-full">
                                            {{ ucfirst(Auth::user()->level) }}
                                        </span>
                                        @if(Auth::user()->identity_verified)
                                            <span class="px-2 py-1 bg-forest-600/10 text-forest-700 text-xs font-semibold rounded-full">
                                                Vérifié
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <!-- Menu Links -->
                            <div class="py-2">
                                @if(Auth::user()->isPrestataire())
                                    <a href="{{ route('prestataire.dashboard') }}" class="block px-4 py-3 hover:bg-ink-100/30 transition text-ink-700 font-medium">
                                        Dashboard
                                    </a>
                                    <a href="{{ route('prestataire.profile', Auth::user()->slug) }}" class="block px-4 py-3 hover:bg-ink-100/30 transition text-ink-700 font-medium">
                                        Mon profil public
                                    </a>
                                    <a href="{{ route('prestataire.services.index') }}" class="block px-4 py-3 hover:bg-ink-100/30 transition text-ink-700 font-medium">
                                        Mes services
                                    </a>
                                    <a href="{{ route('prestataire.wallet') }}" class="block px-4 py-3 hover:bg-ink-100/30 transition text-ink-700 font-medium">
                                        Portefeuille
                                        <span class="text-xs text-ink-400 ml-2">{{ number_format(Auth::user()->wallet_balance ?? 0, 0) }} FCFA</span>
                                    </a>
                                    <a href="{{ route('prestataire.subscription') }}" class="block px-4 py-3 hover:bg-ink-100/30 transition text-ink-700 font-medium">
                                        Abonnement
                                        <span class="text-xs text-ink-400 ml-2">{{ Auth::user()->currentPlan()?->name ?? 'Gratuit' }}</span>
                                    </a>
                                    <a href="{{ route('prestataire.statistics') }}" class="block px-4 py-3 hover:bg-ink-100/30 transition text-ink-700 font-medium">
                                        Statistiques
                                    </a>
                                @else
                                    <a href="{{ route('client.dashboard') }}" class="block px-4 py-3 hover:bg-ink-100/30 transition text-ink-700 font-medium">
                                        Dashboard
                                    </a>
                                    <a href="{{ route('client.dashboard') }}" class="block px-4 py-3 hover:bg-ink-100/30 transition text-ink-700 font-medium">
                                        Mes commandes
                                    </a>
                                @endif
                            </div>

                            <div class="border-t border-ink-100 py-2">
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-3 hover:bg-ink-100/30 transition text-ink-700 font-medium">
                                    Paramètres
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left block px-4 py-3 hover:bg-red-50 transition text-red-700 font-medium">
                                        Déconnexion
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center gap-1">
                    <livewire:notification-bell />

                    <button x-data @click="$dispatch('toggle-mobile-menu')" class="text-ink-700 hover:text-ink-900 p-2 rounded-lg hover:bg-ink-100/40 transition">
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
             class="fixed inset-0 bg-ink-900/50 z-40"
             style="display:none;"></div>

        <div x-show="open"
             x-transition:enter="transition ease-out duration-200 transform"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-150 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             class="fixed top-0 left-0 h-full w-72 bg-cream-50 z-50 shadow-2xl flex flex-col overflow-y-auto"
             style="display:none;">

            <div class="flex items-center justify-between px-5 py-4 border-b border-ink-100">
                <a href="{{ route('home') }}" class="flex items-center">
                    <x-logo-lockup class="h-9 w-auto" />
                </a>
                <button @click="open = false" class="p-2 rounded-lg hover:bg-ink-100/40 transition text-ink-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <nav class="flex-1 px-4 py-6 space-y-1">
                <a href="{{ route('home') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium text-ink-700 hover:bg-terracotta-50 hover:text-terracotta-700 transition">
                    <x-app-icon name="home" class="w-4 h-4" />
                    Accueil
                </a>
                <a href="{{ route('services.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium text-ink-700 hover:bg-terracotta-50 hover:text-terracotta-700 transition">
                    <x-app-icon name="box" class="w-4 h-4" />
                    Services
                </a>
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium text-ink-700 hover:bg-terracotta-50 hover:text-terracotta-700 transition {{ request()->routeIs('*.dashboard') ? 'bg-terracotta-50 text-terracotta-700' : '' }}">
                    <x-app-icon name="cog" class="w-4 h-4" />
                    Dashboard
                </a>
                @if(Auth::user()->isPrestataire())
                    <a href="{{ route('prestataire.services.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium text-ink-700 hover:bg-terracotta-50 hover:text-terracotta-700 transition">
                        <x-app-icon name="briefcase" class="w-4 h-4" />
                        Mes services
                    </a>
                    <a href="{{ route('service-requests.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium text-ink-700 hover:bg-terracotta-50 hover:text-terracotta-700 transition">
                        <x-app-icon name="clipboard" class="w-4 h-4" />
                        Opportunités
                    </a>
                    <a href="{{ route('prestataire.wallet') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium text-ink-700 hover:bg-terracotta-50 hover:text-terracotta-700 transition">
                        <x-app-icon name="banknotes" class="w-4 h-4" />
                        Portefeuille
                    </a>
                    <a href="{{ route('prestataire.subscription') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium text-ink-700 hover:bg-terracotta-50 hover:text-terracotta-700 transition">
                        <x-app-icon name="sparkles" class="w-4 h-4" />
                        Abonnement
                    </a>
                    <a href="{{ route('prestataire.statistics') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium text-ink-700 hover:bg-terracotta-50 hover:text-terracotta-700 transition">
                        <x-app-icon name="chart-bar" class="w-4 h-4" />
                        Statistiques
                    </a>
                @else
                    <a href="{{ route('service-requests.index', ['mine' => 1]) }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium text-ink-700 hover:bg-terracotta-50 hover:text-terracotta-700 transition">
                        <x-app-icon name="clipboard" class="w-4 h-4" />
                        Mes demandes
                    </a>
                @endif
                <a href="{{ route('conversations.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium text-ink-700 hover:bg-terracotta-50 hover:text-terracotta-700 transition {{ request()->routeIs('conversations.*') ? 'bg-terracotta-50 text-terracotta-700' : '' }}">
                    <x-app-icon name="chat" class="w-4 h-4" />
                    Messages
                </a>
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium text-ink-700 hover:bg-terracotta-50 hover:text-terracotta-700 transition">
                    <x-app-icon name="cog" class="w-4 h-4" />
                    Paramètres
                </a>
            </nav>

            <div class="px-4 py-4 border-t border-ink-100">
                <div class="flex items-center gap-3 mb-4">
                    <img src="{{ Auth::user()->avatar ? Storage::url(Auth::user()->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) }}" alt="{{ Auth::user()->name }}" class="w-10 h-10 rounded-full border border-ink-200">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-ink-900 truncate">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-ink-400">{{ ucfirst(Auth::user()->role) }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-red-50 hover:bg-red-100 text-red-700 font-medium rounded-lg transition text-sm">
                        <x-app-icon name="logout" class="w-4 h-4" />
                        Déconnexion
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <div class="container mx-auto px-4 py-4">
        @if(session('success'))
            <div class="bg-forest-600/10 border-l-4 border-forest-600 p-4 mb-4 rounded">
                <p class="text-forest-700 font-medium">{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-600 p-4 mb-4 rounded">
                <p class="text-red-700 font-medium">{{ session('error') }}</p>
            </div>
        @endif

        @if(session('info'))
            <div class="bg-terracotta-50 border-l-4 border-terracotta-600 p-4 mb-4 rounded">
                <p class="text-terracotta-700 font-medium">{{ session('info') }}</p>
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
