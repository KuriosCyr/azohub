<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Azohub') }} - Dashboard</title>

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
                    <a href="{{ route('services.index') }}" class="text-gray-700 hover:text-blue-600 font-semibold transition">
                        Services
                    </a>
                    <a href="{{ route('dashboard') }}" class="text-blue-600 font-semibold">
                        Dashboard
                    </a>
                </div>

                <!-- User Menu -->
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <img src="{{ Auth::user()->avatar ? Storage::url(Auth::user()->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) }}" 
                             alt="{{ Auth::user()->name }}"
                             class="w-8 h-8 rounded-full">
                        <span class="text-sm font-semibold text-gray-700">{{ Auth::user()->name }}</span>
                    </div>

                    <!-- Dropdown -->
                    <div x-data="{ open: false }" @click.away="open = false" class="relative">
                        <button @click="open = !open" class="text-gray-700 hover:text-blue-600 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <div x-show="open" 
                             x-transition
                             class="absolute right-0 top-full mt-2 w-48 bg-white rounded-2xl shadow-2xl py-2 z-50 border border-gray-100">
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-3 hover:bg-blue-50 transition text-gray-700">
                                ⚙️ Paramètres
                            </a>
                            
                            @if(Auth::user()->isPrestataire())
                                <a href="{{ route('prestataire.profile', Auth::id()) }}" class="block px-4 py-3 hover:bg-blue-50 transition text-gray-700">
                                    👤 Mon profil public
                                </a>
                            @endif
                            
                            <div class="border-t border-gray-100 my-2"></div>
                            
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-3 hover:bg-red-50 transition text-red-600 font-semibold">
                                    🚪 Déconnexion
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-4">
            <p class="text-green-700">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4">
            <p class="text-red-700">{{ session('error') }}</p>
        </div>
    @endif

    @if(session('info'))
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-4">
            <p class="text-blue-700">{{ session('info') }}</p>
        </div>
    @endif

    <!-- Page Content -->
    <main>
        {{ $slot }}
    </main>

    @livewireScripts
    @stack('scripts')
</body>
</html>