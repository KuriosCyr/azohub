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

    <!-- Scripts (Alpine.js est déjà bundlé et démarré dans resources/js/app.js) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center gap-1">
                        <span class="text-2xl font-black text-blue-900">Azo</span><span class="text-2xl font-black text-yellow-400">hub</span>
                        <span class="ml-1 inline-flex items-center justify-center w-5 h-3 rounded-sm overflow-hidden">
                            <svg viewBox="0 0 45 30" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                                <rect width="45" height="30" fill="#E8112D"/>
                                <rect width="45" height="15" fill="#FCD116"/>
                                <rect width="18" height="30" fill="#008751"/>
                            </svg>
                        </span>
                    </a>
                </div>

                <!-- Navigation principale (desktop) -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('home') }}" class="text-gray-700 hover:text-blue-900 font-semibold transition {{ request()->routeIs('home') ? 'text-blue-900 border-b-2 border-blue-900' : '' }}">
                        Accueil
                    </a>

                    <!-- Dropdown Catégories -->
                    <div x-data="{ open: false }" @click.away="open = false" class="relative">
                        <button @click="open = !open" class="text-gray-700 hover:text-blue-900 font-semibold flex items-center gap-1 transition">
                            Catégories
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <div x-show="open"
                             x-transition
                             class="absolute top-full left-0 mt-2 w-80 bg-white rounded-2xl shadow-2xl py-3 z-50 border border-gray-100"
                             style="display: none;">
                            @php
                                $categories = \App\Models\Category::where('is_active', true)->get();
                            @endphp

                            <div class="max-h-96 overflow-y-auto">
                                @foreach($categories as $category)
                                    <a href="{{ route('services.index', ['category' => $category->slug]) }}"
                                       class="flex items-center justify-between px-4 py-3 hover:bg-blue-50 transition group">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-blue-50 group-hover:bg-blue-100 flex items-center justify-center text-blue-700 transition">
                                                @php
                                                    $catIcons = [
                                                        'Hammer'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z"/>',
                                                        'Code'        => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5"/>',
                                                        'Home'        => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>',
                                                        'GraduationCap' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/>',
                                                        'Calendar'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>',
                                                        'Truck'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>',
                                                        'Sparkles'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"/>',
                                                        'Wrench'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21.75 6.75a4.5 4.5 0 01-4.884 4.484c-1.076-.091-2.264.071-2.95.904l-7.152 8.684a2.548 2.548 0 11-3.586-3.586l8.684-7.152c.833-.686.995-1.874.904-2.95a4.5 4.5 0 016.336-4.486l-3.276 3.276a3.004 3.004 0 002.25 2.25l3.276-3.276c.256.565.398 1.192.398 1.852z"/>',
                                                        'FileText'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>',
                                                        'Heart'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>',
                                                    ];
                                                    $svgPath = $catIcons[$category->icon] ?? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>';
                                                @endphp
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $svgPath !!}</svg>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-gray-800 group-hover:text-blue-900">{{ $category->name }}</p>
                                                <p class="text-xs text-gray-500">{{ Str::limit($category->description ?? '', 40) }}</p>
                                            </div>
                                        </div>
                                        <svg class="w-4 h-4 text-gray-300 group-hover:text-blue-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </a>
                                @endforeach
                            </div>

                            <div class="border-t border-gray-100 mt-2 pt-2">
                                <a href="{{ route('services.index') }}" class="block px-4 py-3 text-blue-600 font-bold hover:bg-blue-50 transition">
                                    Voir toutes les catégories →
                                </a>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('services.index') }}" class="text-gray-700 hover:text-blue-900 font-semibold transition {{ request()->routeIs('services.*') && !request()->routeIs('prestataire.services.*') ? 'text-blue-900 border-b-2 border-blue-900' : '' }}">
                        Tous les services
                    </a>

                    <a href="{{ route('how-it-works') }}" class="text-gray-700 hover:text-blue-900 font-semibold transition {{ request()->routeIs('how-it-works') ? 'text-blue-900 border-b-2 border-blue-900' : '' }}">
                        Comment ça marche
                    </a>

                    @auth
                        <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-blue-900 font-semibold transition {{ request()->routeIs('*.dashboard') ? 'text-blue-900 border-b-2 border-blue-900' : '' }}">
                            Dashboard
                        </a>

                        @if(Auth::user()->isPrestataire())
                            <a href="{{ route('prestataire.services.index') }}" class="text-gray-700 hover:text-blue-900 font-semibold transition {{ request()->routeIs('prestataire.services.*') ? 'text-blue-900 border-b-2 border-blue-900' : '' }}">
                                Mes services
                            </a>
                        @endif
                    @endauth
                </div>

                <!-- Auth / User Menu -->
                <div class="hidden md:flex items-center space-x-4">
                    @auth
                        <!-- User Dropdown -->
                        <div x-data="{ open: false }" @click.away="open = false" class="relative">
                            <button @click="open = !open" class="flex items-center gap-3 hover:bg-gray-50 rounded-2xl px-4 py-2 transition">
                                <img src="{{ Auth::user()->avatar ? Storage::url(Auth::user()->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) }}"
                                     alt="{{ Auth::user()->name }}"
                                     class="w-9 h-9 rounded-full border-2 border-blue-100">
                                <div class="text-left">
                                    <p class="text-sm font-bold text-gray-900">{{ Str::limit(Auth::user()->name, 18) }}</p>
                                    <p class="text-xs text-gray-500">{{ ucfirst(Auth::user()->role) }}</p>
                                </div>
                                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                        <a href="{{ route('prestataire.dashboard') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-blue-50 transition text-gray-700 font-semibold">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                            Dashboard
                                        </a>
                                        <a href="{{ route('prestataire.profile', Auth::user()->id) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-blue-50 transition text-gray-700 font-semibold">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            Mon profil public
                                        </a>
                                        <a href="{{ route('prestataire.services.index') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-blue-50 transition text-gray-700 font-semibold">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                            Mes services
                                        </a>
                                        <a href="#" class="flex items-center gap-3 px-4 py-3 hover:bg-blue-50 transition text-gray-700 font-semibold">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                            Portefeuille
                                            <span class="text-xs text-gray-500 ml-auto">{{ number_format(Auth::user()->wallet_balance ?? 0, 0) }} F</span>
                                        </a>
                                    @else
                                        <a href="{{ route('client.dashboard') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-blue-50 transition text-gray-700 font-semibold">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                            Dashboard
                                        </a>
                                        <a href="{{ route('client.dashboard') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-blue-50 transition text-gray-700 font-semibold">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                            Mes commandes
                                        </a>
                                    @endif
                                </div>

                                <div class="border-t border-gray-100 py-2">
                                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition text-gray-700 font-semibold">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        Paramètres
                                    </a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 hover:bg-red-50 transition text-red-600 font-semibold">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                            Déconnexion
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Non connecté -->
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-blue-900 font-semibold transition">
                            Connexion
                        </a>
                        <a href="{{ route('register') }}" class="bg-yellow-400 hover:bg-yellow-500 text-blue-900 font-black px-5 py-2.5 rounded-full transition shadow-md text-sm">
                            S'inscrire gratuitement
                        </a>
                    @endauth
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
        <!-- Overlay -->
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

        <!-- Drawer -->
        <div x-show="open"
             x-transition:enter="transition ease-out duration-200 transform"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-150 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             class="fixed top-0 left-0 h-full w-72 bg-white z-50 shadow-2xl flex flex-col overflow-y-auto"
             style="display:none;">

            <!-- Header -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <a href="{{ route('home') }}" class="flex items-center gap-1">
                    <span class="text-xl font-black text-blue-900">Azo</span><span class="text-xl font-black text-yellow-400">hub</span>
                </a>
                <button @click="open = false" class="p-2 rounded-lg hover:bg-gray-100 transition text-gray-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Nav links -->
            <nav class="flex-1 px-4 py-6 space-y-1">
                <a href="{{ route('home') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-700 hover:bg-blue-50 hover:text-blue-900 transition {{ request()->routeIs('home') ? 'bg-blue-50 text-blue-900' : '' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Accueil
                </a>
                <a href="{{ route('services.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-700 hover:bg-blue-50 hover:text-blue-900 transition {{ request()->routeIs('services.*') ? 'bg-blue-50 text-blue-900' : '' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    Tous les services
                </a>
                <a href="{{ route('how-it-works') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-700 hover:bg-blue-50 hover:text-blue-900 transition {{ request()->routeIs('how-it-works') ? 'bg-blue-50 text-blue-900' : '' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Comment ça marche
                </a>

                @auth
                    <div class="border-t border-gray-100 my-3"></div>
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-700 hover:bg-blue-50 hover:text-blue-900 transition">
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
                @else
                    <div class="border-t border-gray-100 my-3"></div>
                    <a href="{{ route('login') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-700 hover:bg-blue-50 hover:text-blue-900 transition">
                        Connexion
                    </a>
                @endauth
            </nav>

            <!-- Bottom -->
            <div class="px-4 py-4 border-t border-gray-100">
                @auth
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
                @else
                    <a href="{{ route('register') }}" class="block w-full text-center bg-yellow-400 hover:bg-yellow-500 text-blue-900 font-black px-4 py-3 rounded-xl transition text-sm">
                        S'inscrire gratuitement
                    </a>
                @endauth
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success') || session('error') || session('info'))
    <div class="container mx-auto px-4 pt-4">
        @if(session('success'))
            <div class="flex items-center gap-3 bg-green-50 border border-green-200 p-4 mb-3 rounded-xl">
                <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-green-700 font-semibold">{{ session('success') }}</p>
            </div>
        @endif
        @if(session('error'))
            <div class="flex items-center gap-3 bg-red-50 border border-red-200 p-4 mb-3 rounded-xl">
                <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-red-700 font-semibold">{{ session('error') }}</p>
            </div>
        @endif
        @if(session('info'))
            <div class="flex items-center gap-3 bg-blue-50 border border-blue-200 p-4 mb-3 rounded-xl">
                <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-blue-700 font-semibold">{{ session('info') }}</p>
            </div>
        @endif
    </div>
    @endif

    <!-- Page Content -->
    <main>
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white pt-14 pb-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10 mb-10">
                <!-- About -->
                <div>
                    <div class="flex items-center gap-1.5 mb-4">
                        <span class="text-xl font-black text-white">Azo</span><span class="text-xl font-black text-yellow-400">hub</span>
                        <span class="inline-flex items-center justify-center w-5 h-3 rounded-sm overflow-hidden ml-1">
                            <svg viewBox="0 0 45 30" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                                <rect width="45" height="30" fill="#E8112D"/>
                                <rect width="45" height="15" fill="#FCD116"/>
                                <rect width="18" height="30" fill="#008751"/>
                            </svg>
                        </span>
                    </div>
                    <p class="text-gray-400 text-sm leading-relaxed mb-5">
                        La plateforme n°1 pour trouver des prestataires de confiance au Bénin.
                    </p>
                    <div class="flex gap-3">
                        <!-- Facebook -->
                        <a href="#" class="w-9 h-9 bg-gray-800 hover:bg-blue-600 rounded-full flex items-center justify-center transition">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <!-- Instagram -->
                        <a href="#" class="w-9 h-9 bg-gray-800 hover:bg-pink-600 rounded-full flex items-center justify-center transition">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        <!-- Twitter/X -->
                        <a href="#" class="w-9 h-9 bg-gray-800 hover:bg-sky-500 rounded-full flex items-center justify-center transition">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="font-bold mb-4 text-white">Liens rapides</h4>
                    <ul class="space-y-2.5 text-gray-400 text-sm">
                        <li><a href="{{ route('home') }}" class="hover:text-white transition">Accueil</a></li>
                        <li><a href="{{ route('services.index') }}" class="hover:text-white transition">Services</a></li>
                        <li><a href="{{ route('how-it-works') }}" class="hover:text-white transition">Comment ça marche</a></li>
                        <li><a href="{{ route('faq') }}" class="hover:text-white transition">FAQ</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-white transition">Contact</a></li>
                    </ul>
                </div>

                <!-- Categories populaires -->
                <div>
                    <h4 class="font-bold mb-4 text-white">Catégories populaires</h4>
                    <ul class="space-y-2.5 text-gray-400 text-sm">
                        @php
                            $popularCategories = \App\Models\Category::where('is_active', true)
                                ->withCount('services')
                                ->orderBy('services_count', 'desc')
                                ->take(5)
                                ->get();
                        @endphp
                        @foreach($popularCategories as $cat)
                            <li>
                                <a href="{{ route('services.index', ['category' => $cat->slug]) }}" class="hover:text-white transition">
                                    {{ $cat->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Contact -->
                <div>
                    <h4 class="font-bold mb-4 text-white">Contact & Support</h4>
                    <ul class="space-y-3 text-gray-400 text-sm">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-yellow-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            contact@azohub.bj
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-yellow-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            +229 XX XX XX XX
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-yellow-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Cotonou, Bénin
                        </li>
                    </ul>
                    <div class="mt-5">
                        @guest
                            <a href="{{ route('register') }}" class="inline-block bg-yellow-400 hover:bg-yellow-500 text-blue-900 font-bold px-4 py-2 rounded-full transition text-sm">
                                Devenir prestataire
                            </a>
                        @else
                            @if(!Auth::user()->isPrestataire())
                                <a href="{{ route('register') }}?role=prestataire" class="inline-block bg-yellow-400 hover:bg-yellow-500 text-blue-900 font-bold px-4 py-2 rounded-full transition text-sm">
                                    Devenir prestataire
                                </a>
                            @endif
                        @endguest
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-800 pt-6 text-center text-gray-500 text-sm">
                <p>&copy; {{ date('Y') }} Azohub. Tous droits réservés.</p>
                <div class="mt-2 flex flex-wrap justify-center gap-x-4 gap-y-1">
                    <a href="{{ route('terms') }}" class="hover:text-white transition">Conditions d'utilisation</a>
                    <span class="text-gray-700">•</span>
                    <a href="{{ route('privacy') }}" class="hover:text-white transition">Politique de confidentialité</a>
                    <span class="text-gray-700">•</span>
                    <a href="{{ route('faq') }}" class="hover:text-white transition">FAQ</a>
                    <span class="text-gray-700">•</span>
                    <a href="{{ route('contact') }}" class="hover:text-white transition">Contact</a>
                </div>
            </div>
        </div>
    </footer>

    @livewireScripts
    @stack('scripts')
</body>
</html>
