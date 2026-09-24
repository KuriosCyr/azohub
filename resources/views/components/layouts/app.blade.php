@props([
    'title' => null,
    'description' => null,
    'ogImage' => null,
    'ogType' => 'website',
    'canonical' => null,
    'noindex' => false,
    'jsonLd' => null,
])
@php
    // Titre : le contenu spécifique en premier (ce que Google et le visiteur voient en premier
    // dans une liste de résultats), le nom de la marque en dernier — jamais l'inverse pour une
    // page de contenu, sinon toutes les pages se ressemblent dans les résultats de recherche.
    $pageTitle = $title ? "{$title} | Azohub" : config('app.name', 'Azohub') . ' - Plateforme de services au Bénin';
    $pageDescription = $description ?? "Azohub met en relation clients et prestataires qualifiés partout au Bénin : trouvez un service ou proposez le vôtre en toute confiance.";
    // 'design-exports/...' pointait vers resources/design-exports (jamais servi publiquement,
    // ne fait pas partie de public/) : 404 sur toutes les pages sans image explicite. En plus,
    // les crawlers WhatsApp/Facebook n'affichent pas de SVG en aperçu — il faut du PNG/JPG.
    $pageImage = $ogImage ?? asset('images/og-image.png');
    $pageCanonical = $canonical ?? url()->current();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <link rel="canonical" href="{{ $pageCanonical }}">
    @if($noindex)
        <meta name="robots" content="noindex, nofollow">
    @endif

    {{-- Open Graph / réseaux sociaux --}}
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:site_name" content="Azohub">
    <meta property="og:title" content="{{ $title ?? config('app.name', 'Azohub') }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:image" content="{{ $pageImage }}">
    <meta property="og:url" content="{{ $pageCanonical }}">
    <meta property="og:locale" content="fr_BJ">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? config('app.name', 'Azohub') }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $pageImage }}">

    @if($jsonLd)
        <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endif

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
    <!-- Navigation -->
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

                    <!-- Dropdown Catégories -->
                    <div x-data="{ open: false }" @click.away="open = false" class="relative">
                        <button @click="open = !open" class="text-sm font-medium text-ink-500 hover:text-ink-900 flex items-center gap-1 transition">
                            Catégories
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <div x-show="open"
                             x-transition
                             class="absolute top-full left-0 mt-2 w-80 bg-cream-50 rounded-lg shadow-lg py-3 z-50 border border-ink-100"
                             style="display: none;">
                            @php
                                $categories = \App\Models\Category::where('is_active', true)->get();
                            @endphp

                            <div class="max-h-96 overflow-y-auto">
                                @foreach($categories as $category)
                                    <a href="{{ route('services.index', ['category' => $category->slug]) }}"
                                       class="flex items-center justify-between px-4 py-3 hover:bg-terracotta-50 transition group">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-lg bg-terracotta-50 group-hover:bg-terracotta-100 flex items-center justify-center text-terracotta-700 transition">
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
                                                <p class="font-semibold text-ink-900 group-hover:text-terracotta-700">{{ $category->name }}</p>
                                                <p class="text-xs text-ink-400">{{ Str::limit($category->description ?? '', 40) }}</p>
                                            </div>
                                        </div>
                                        <svg class="w-4 h-4 text-ink-300 group-hover:text-terracotta-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </a>
                                @endforeach
                            </div>

                            <div class="border-t border-ink-100 mt-2 pt-2">
                                <a href="{{ route('services.index') }}" class="block px-4 py-3 text-terracotta-600 font-semibold hover:bg-terracotta-50 transition">
                                    Voir toutes les catégories &rarr;
                                </a>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('services.index') }}" class="text-sm font-medium text-ink-500 hover:text-ink-900 transition {{ request()->routeIs('services.*') && !request()->routeIs('prestataire.services.*') ? 'text-ink-900 border-b-2 border-terracotta-600 pb-1' : '' }}">
                        Tous les services
                    </a>

                    <a href="{{ route('how-it-works') }}" class="text-sm font-medium text-ink-500 hover:text-ink-900 transition {{ request()->routeIs('how-it-works') ? 'text-ink-900 border-b-2 border-terracotta-600 pb-1' : '' }}">
                        Comment ça marche
                    </a>

                    @auth
                        @php
                            // Messages non lus, tous canaux confondus (chat direct + chat de
                            // commande), pour les deux rôles — sert de badge au lien Messages.
                            $unreadMessagesCount = \App\Models\ConversationMessage::whereHas('conversation', function ($q) {
                                    $q->where('client_id', Auth::id())->orWhere('prestataire_id', Auth::id());
                                })
                                ->where('sender_id', '!=', Auth::id())
                                ->where('is_read', false)
                                ->count()
                                + \App\Models\Message::where('receiver_id', Auth::id())->where('is_read', false)->count();
                        @endphp
                        <a href="{{ route('dashboard') }}" class="text-sm font-medium text-ink-500 hover:text-ink-900 transition {{ request()->routeIs('*.dashboard') ? 'text-ink-900 border-b-2 border-terracotta-600 pb-1' : '' }}">
                            Dashboard
                        </a>

                        @if(Auth::user()->isPrestataire())
                            @php
                                // Opportunités ouvertes dont la catégorie correspond à au moins un des
                                // services du prestataire connecté (pas toutes les opportunités).
                                $myOpenOpportunitiesCount = \App\Models\ServiceRequest::open()
                                    ->whereIn('category_id', \App\Models\Service::where('user_id', Auth::id())->pluck('category_id')->unique())
                                    ->count();
                            @endphp
                            <a href="{{ route('prestataire.services.index') }}" class="text-sm font-medium text-ink-500 hover:text-ink-900 transition {{ request()->routeIs('prestataire.services.*') ? 'text-ink-900 border-b-2 border-terracotta-600 pb-1' : '' }}">
                                Mes services
                            </a>
                            <a href="{{ route('service-requests.index') }}" class="text-sm font-medium text-ink-500 hover:text-ink-900 transition inline-flex items-center gap-1.5 {{ request()->routeIs('service-requests.*') ? 'text-ink-900 border-b-2 border-terracotta-600 pb-1' : '' }}">
                                Opportunités
                                @if($myOpenOpportunitiesCount > 0)
                                    <span class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-terracotta-600 text-cream-50 text-[10px] font-semibold leading-none">
                                        {{ $myOpenOpportunitiesCount > 99 ? '99+' : $myOpenOpportunitiesCount }}
                                    </span>
                                @endif
                            </a>
                        @else
                            <a href="{{ route('service-requests.index', ['mine' => 1]) }}" class="text-sm font-medium text-ink-500 hover:text-ink-900 transition {{ request()->routeIs('service-requests.*') ? 'text-ink-900 border-b-2 border-terracotta-600 pb-1' : '' }}">
                                Mes demandes
                            </a>
                        @endif

                        <a href="{{ route('conversations.index') }}" class="text-sm font-medium text-ink-500 hover:text-ink-900 transition inline-flex items-center gap-1.5 {{ request()->routeIs('conversations.*') ? 'text-ink-900 border-b-2 border-terracotta-600 pb-1' : '' }}">
                            Messages
                            @if($unreadMessagesCount > 0)
                                <span class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-terracotta-600 text-cream-50 text-[10px] font-semibold leading-none">
                                    {{ $unreadMessagesCount > 99 ? '99+' : $unreadMessagesCount }}
                                </span>
                            @endif
                        </a>
                    @endauth
                </div>

                <!-- Auth / User Menu -->
                <div class="flex items-center gap-1 md:gap-4">
                    @auth
                        {{-- Une seule cloche pour desktop et mobile (deux instances = 2x les requêtes de polling). --}}
                        <livewire:notification-bell />

                        <!-- User Dropdown (desktop) -->
                        <div x-data="{ open: false }" @click.away="open = false" class="relative hidden md:block">
                            <button @click="open = !open" class="flex items-center gap-3 hover:bg-ink-100/40 rounded-lg px-3 py-2 transition">
                                <img src="{{ Auth::user()->avatar ? Storage::url(Auth::user()->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) }}"
                                     alt="{{ Auth::user()->name }}"
                                     class="w-9 h-9 rounded-full border border-ink-200">
                                <div class="text-left">
                                    <p class="text-sm font-semibold text-ink-900">{{ Str::limit(Auth::user()->name, 18) }}</p>
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
                                                {{ Auth::user()->level_label }}
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
                                        <a href="{{ route('prestataire.dashboard') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-ink-100/30 transition text-ink-700 font-medium">
                                            <x-app-icon name="cog" class="w-4 h-4 text-ink-300" />
                                            Dashboard
                                        </a>
                                        <a href="{{ route('prestataire.profile', Auth::user()->slug) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-ink-100/30 transition text-ink-700 font-medium">
                                            <x-app-icon name="user" class="w-4 h-4 text-ink-300" />
                                            Mon profil public
                                        </a>
                                        <a href="{{ route('prestataire.services.index') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-ink-100/30 transition text-ink-700 font-medium">
                                            <x-app-icon name="briefcase" class="w-4 h-4 text-ink-300" />
                                            Mes services
                                        </a>
                                        <a href="{{ route('prestataire.wallet') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-ink-100/30 transition text-ink-700 font-medium">
                                            <x-app-icon name="banknotes" class="w-4 h-4 text-ink-300" />
                                            Portefeuille
                                            <span class="text-xs text-ink-400 ml-auto">{{ number_format(Auth::user()->wallet_balance ?? 0, 0, ',', ' ') }} F</span>
                                        </a>
                                        <a href="{{ route('prestataire.subscription') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-ink-100/30 transition text-ink-700 font-medium">
                                            <x-app-icon name="sparkles" class="w-4 h-4 text-ink-300" />
                                            Abonnement
                                            <span class="text-xs text-ink-400 ml-auto">{{ Auth::user()->currentPlan()?->name ?? 'Gratuit' }}</span>
                                        </a>
                                        <a href="{{ route('prestataire.statistics') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-ink-100/30 transition text-ink-700 font-medium">
                                            <x-app-icon name="chart-bar" class="w-4 h-4 text-ink-300" />
                                            Statistiques
                                        </a>
                                    @else
                                        <a href="{{ route('client.dashboard') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-ink-100/30 transition text-ink-700 font-medium">
                                            <x-app-icon name="home" class="w-4 h-4 text-ink-300" />
                                            Dashboard
                                        </a>
                                        <a href="{{ route('client.dashboard') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-ink-100/30 transition text-ink-700 font-medium">
                                            <x-app-icon name="cart" class="w-4 h-4 text-ink-300" />
                                            Mes commandes
                                        </a>
                                    @endif
                                </div>

                                <div class="border-t border-ink-100 py-2">
                                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-ink-100/30 transition text-ink-700 font-medium">
                                        <x-app-icon name="cog" class="w-4 h-4 text-ink-300" />
                                        Paramètres
                                    </a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 hover:bg-red-50 transition text-red-700 font-medium">
                                            <x-app-icon name="logout" class="w-4 h-4" />
                                            Déconnexion
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Non connecté (desktop) -->
                        <div class="hidden md:flex items-center gap-4">
                            <a href="{{ route('login') }}" class="text-sm font-medium text-ink-500 hover:text-ink-900 transition">
                                Connexion
                            </a>
                            <a href="{{ route('register') }}" class="bg-terracotta-600 hover:bg-terracotta-700 text-cream-50 font-semibold px-5 py-2.5 rounded-lg transition text-sm">
                                S'inscrire gratuitement
                            </a>
                        </div>
                    @endauth

                    <!-- Mobile menu button -->
                    <button x-data @click="$dispatch('toggle-mobile-menu')" class="md:hidden text-ink-700 hover:text-ink-900 p-2 rounded-lg hover:bg-ink-100/40 transition">
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
             class="fixed inset-0 bg-ink-900/50 z-40"
             style="display:none;"></div>

        <!-- Drawer -->
        <div x-show="open"
             x-transition:enter="transition ease-out duration-200 transform"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-150 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             class="fixed top-0 left-0 h-full w-72 bg-cream-50 z-50 shadow-2xl flex flex-col overflow-y-auto"
             style="display:none;">

            <!-- Header -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-ink-100">
                <a href="{{ route('home') }}" class="flex items-center">
                    <x-logo-lockup class="h-9 w-auto" />
                </a>
                <button @click="open = false" class="p-2 rounded-lg hover:bg-ink-100/40 transition text-ink-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Nav links -->
            <nav class="flex-1 px-4 py-6 space-y-1">
                <a href="{{ route('home') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium text-ink-700 hover:bg-terracotta-50 hover:text-terracotta-700 transition {{ request()->routeIs('home') ? 'bg-terracotta-50 text-terracotta-700' : '' }}">
                    <x-app-icon name="home" class="w-4 h-4" />
                    Accueil
                </a>
                <a href="{{ route('services.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium text-ink-700 hover:bg-terracotta-50 hover:text-terracotta-700 transition {{ request()->routeIs('services.*') ? 'bg-terracotta-50 text-terracotta-700' : '' }}">
                    <x-app-icon name="box" class="w-4 h-4" />
                    Tous les services
                </a>
                <a href="{{ route('how-it-works') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium text-ink-700 hover:bg-terracotta-50 hover:text-terracotta-700 transition {{ request()->routeIs('how-it-works') ? 'bg-terracotta-50 text-terracotta-700' : '' }}">
                    <x-app-icon name="question-circle" class="w-4 h-4" />
                    Comment ça marche
                </a>

                @auth
                    <div class="border-t border-ink-100 my-3"></div>
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
                            @if($myOpenOpportunitiesCount > 0)
                                <span class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-terracotta-600 text-cream-50 text-[10px] font-semibold leading-none ml-auto">
                                    {{ $myOpenOpportunitiesCount > 99 ? '99+' : $myOpenOpportunitiesCount }}
                                </span>
                            @endif
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
                        @if($unreadMessagesCount > 0)
                            <span class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-terracotta-600 text-cream-50 text-[10px] font-semibold leading-none ml-auto">
                                {{ $unreadMessagesCount > 99 ? '99+' : $unreadMessagesCount }}
                            </span>
                        @endif
                    </a>
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium text-ink-700 hover:bg-terracotta-50 hover:text-terracotta-700 transition">
                        <x-app-icon name="cog" class="w-4 h-4" />
                        Paramètres
                    </a>
                @else
                    <div class="border-t border-ink-100 my-3"></div>
                    <a href="{{ route('login') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium text-ink-700 hover:bg-terracotta-50 hover:text-terracotta-700 transition">
                        Connexion
                    </a>
                @endauth
            </nav>

            <!-- Bottom -->
            <div class="px-4 py-4 border-t border-ink-100">
                @auth
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
                @else
                    <a href="{{ route('register') }}" class="block w-full text-center bg-terracotta-600 hover:bg-terracotta-700 text-cream-50 font-semibold px-4 py-3 rounded-lg transition text-sm">
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
            <div class="flex items-center gap-3 bg-forest-600/10 border border-forest-600/30 p-4 mb-3 rounded-lg">
                <svg class="w-5 h-5 text-forest-700 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-forest-700 font-medium">{{ session('success') }}</p>
            </div>
        @endif
        @if(session('error'))
            <div class="flex items-center gap-3 bg-red-50 border border-red-200 p-4 mb-3 rounded-lg">
                <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-red-700 font-medium">{{ session('error') }}</p>
            </div>
        @endif
        @if(session('info'))
            <div class="flex items-center gap-3 bg-terracotta-50 border border-terracotta-600/30 p-4 mb-3 rounded-lg">
                <svg class="w-5 h-5 text-terracotta-700 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-terracotta-700 font-medium">{{ session('info') }}</p>
            </div>
        @endif
    </div>
    @endif

    <!-- Page Content -->
    <main>
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-ink-900 text-cream-100 pt-14 pb-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10 mb-10">
                <!-- About -->
                <div>
                    <div class="flex items-center mb-4">
                        <x-logo-lockup class="h-9 w-auto" :dark="true" />
                    </div>
                    <p class="text-cream-100/60 text-sm leading-relaxed mb-5">
                        La plateforme n°1 pour trouver des prestataires de confiance au Bénin.
                    </p>
                    <div class="flex gap-3">
                        <!-- Facebook -->
                        <a href="#" class="w-9 h-9 bg-cream-50/10 hover:bg-terracotta-600 rounded-full flex items-center justify-center transition">
                            <svg class="w-4 h-4 text-cream-50" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <!-- Instagram -->
                        <a href="#" class="w-9 h-9 bg-cream-50/10 hover:bg-terracotta-600 rounded-full flex items-center justify-center transition">
                            <svg class="w-4 h-4 text-cream-50" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        <!-- Twitter/X -->
                        <a href="#" class="w-9 h-9 bg-cream-50/10 hover:bg-terracotta-600 rounded-full flex items-center justify-center transition">
                            <svg class="w-4 h-4 text-cream-50" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="font-semibold mb-4 text-cream-50">Liens rapides</h4>
                    <ul class="space-y-2.5 text-cream-100/60 text-sm">
                        <li><a href="{{ route('home') }}" class="hover:text-cream-50 transition">Accueil</a></li>
                        <li><a href="{{ route('services.index') }}" class="hover:text-cream-50 transition">Services</a></li>
                        <li><a href="{{ route('how-it-works') }}" class="hover:text-cream-50 transition">Comment ça marche</a></li>
                        <li><a href="{{ route('faq') }}" class="hover:text-cream-50 transition">FAQ</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-cream-50 transition">Contact</a></li>
                    </ul>
                </div>

                <!-- Categories populaires -->
                <div>
                    <h4 class="font-semibold mb-4 text-cream-50">Catégories populaires</h4>
                    <ul class="space-y-2.5 text-cream-100/60 text-sm">
                        @php
                            $popularCategories = \App\Models\Category::where('is_active', true)
                                ->withCount(['services' => fn ($q) => $q->active()])
                                ->orderBy('services_count', 'desc')
                                ->take(5)
                                ->get();
                        @endphp
                        @foreach($popularCategories as $cat)
                            <li>
                                <a href="{{ route('services.index', ['category' => $cat->slug]) }}" class="hover:text-cream-50 transition">
                                    {{ $cat->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Contact -->
                <div>
                    <h4 class="font-semibold mb-4 text-cream-50">Contact &amp; Support</h4>
                    <ul class="space-y-3 text-cream-100/60 text-sm">
                        <li class="flex items-center gap-2">
                            <x-app-icon name="envelope" class="w-4 h-4 text-ochre-500 flex-shrink-0" />
                            contact@azohub.bj
                        </li>
                        @if(config('services.azohub.support_phone'))
                            <li class="flex items-center gap-2">
                                <x-app-icon name="phone" class="w-4 h-4 text-ochre-500 flex-shrink-0" />
                                {{ config('services.azohub.support_phone') }}
                            </li>
                        @endif
                        <li class="flex items-center gap-2">
                            <x-app-icon name="map-pin" class="w-4 h-4 text-ochre-500 flex-shrink-0" />
                            Cotonou, Bénin
                        </li>
                    </ul>
                    <div class="mt-5">
                        @guest
                            <a href="{{ route('register') }}" class="inline-block bg-terracotta-600 hover:bg-terracotta-700 text-cream-50 font-semibold px-4 py-2 rounded-lg transition text-sm">
                                Devenir prestataire
                            </a>
                        @else
                            @if(!Auth::user()->isPrestataire())
                                <a href="{{ route('register') }}?role=prestataire" class="inline-block bg-terracotta-600 hover:bg-terracotta-700 text-cream-50 font-semibold px-4 py-2 rounded-lg transition text-sm">
                                    Devenir prestataire
                                </a>
                            @endif
                        @endguest
                    </div>
                </div>
            </div>

            <div class="border-t border-cream-50/10 pt-6 text-center text-cream-100/50 text-sm">
                <p>&copy; {{ date('Y') }} Azohub. Tous droits réservés.</p>
                <div class="mt-2 flex flex-wrap justify-center gap-x-4 gap-y-1">
                    <a href="{{ route('terms') }}" class="hover:text-cream-50 transition">Conditions d'utilisation</a>
                    <span class="text-cream-100/20">•</span>
                    <a href="{{ route('privacy') }}" class="hover:text-cream-50 transition">Politique de confidentialité</a>
                    <span class="text-cream-100/20">•</span>
                    <a href="{{ route('faq') }}" class="hover:text-cream-50 transition">FAQ</a>
                    <span class="text-cream-100/20">•</span>
                    <a href="{{ route('contact') }}" class="hover:text-cream-50 transition">Contact</a>
                </div>
            </div>
        </div>
    </footer>

    {{-- Livewire.start() est appelé depuis resources/js/app.js (bundle Vite partagé
         avec Alpine) plutôt que via @livewireScripts, pour éviter deux instances
         Alpine concurrentes. @livewireScriptConfig fournit l'URL de mise à jour et le
         jeton CSRF au bundle (et l'empêche de se démarrer une 2e fois tout seul).
         @livewireStyles (dans le <head>) reste nécessaire. --}}
    @livewireScriptConfig
    @stack('scripts')
</body>
</html>
