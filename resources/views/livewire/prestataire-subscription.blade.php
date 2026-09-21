<div class="py-8 bg-cream min-h-screen">
    <div class="container mx-auto px-4 max-w-5xl">
        <div class="mb-8">
            <h1 class="text-4xl font-serif font-medium text-ink-900 mb-2">Abonnement</h1>
            <p class="text-ink-500">Réduisez votre commission et publiez plus de services avec un plan payant.</p>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-forest-600/10 border border-forest-600/20 text-forest-700 px-4 py-3 rounded-xl text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- Plan actuel --}}
        <div class="bg-ink-900 text-cream-50 rounded-xl p-6 mb-10 flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-ink-300 text-sm mb-1">Plan actuel</p>
                <p class="text-2xl font-serif font-medium">{{ $this->currentPlan?->name ?? 'Gratuit' }}</p>
                @if($this->activeSubscription)
                    <p class="text-ink-300 text-sm mt-1">Expire le {{ $this->activeSubscription->ends_at->format('d/m/Y') }}</p>
                    <label class="flex items-center gap-2 mt-3 cursor-pointer">
                        <input type="checkbox" wire:click="toggleAutoRenew" @checked($this->activeSubscription->auto_renew)
                               class="w-4 h-4 rounded border-ink-300 text-terracotta-600 focus:ring-terracotta-600">
                        <span class="text-xs text-ink-300">Me rappeler et me proposer un renouvellement rapide avant l'expiration</span>
                    </label>
                @endif
            </div>
            <div class="text-right">
                <p class="text-ink-300 text-sm mb-1">Commission actuelle</p>
                <p class="text-2xl font-bold text-ochre-500">{{ number_format(Auth::user()->commissionRate() * 100, 0) }}%</p>
            </div>
            <div class="text-right">
                <p class="text-ink-300 text-sm mb-1">Services publiables</p>
                <p class="text-2xl font-bold text-ochre-500">{{ Auth::user()->maxServices() ?? '∞' }}</p>
                <p class="text-ink-400 text-xs mt-0.5">Niveau {{ ucfirst(Auth::user()->level) }} + plan {{ $this->currentPlan?->name ?? 'Gratuit' }}</p>
            </div>
        </div>

        {{-- Plans disponibles --}}
        <div class="grid md:grid-cols-3 gap-6">
            @foreach($this->plans as $plan)
                @php $isCurrent = $this->currentPlan && $this->currentPlan->id === $plan->id; @endphp
                <div class="relative bg-cream-50 rounded-xl border-2 {{ $plan->is_popular ? 'border-terracotta-600' : 'border-ink-100' }} p-6 flex flex-col">
                    @if($plan->is_popular)
                        <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-terracotta-600 text-cream-50 text-xs font-bold px-3 py-1 rounded-full">
                            Populaire
                        </span>
                    @endif

                    <h3 class="text-xl font-bold text-ink-900 mb-1">{{ $plan->name }}</h3>
                    <p class="text-sm text-ink-500 mb-4">{{ $plan->description }}</p>

                    <p class="mb-4">
                        <span class="text-3xl font-serif font-medium text-ink-900">{{ number_format($plan->price, 0) }}</span>
                        <span class="text-ink-400 text-sm">FCFA / {{ $plan->billing_period === 'yearly' ? 'an' : 'mois' }}</span>
                    </p>

                    <ul class="space-y-2 mb-6 flex-1">
                        @foreach($plan->features ?? [] as $feature)
                            <li class="flex items-start gap-2 text-sm text-ink-600">
                                <svg class="w-4 h-4 text-forest-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ $feature }}
                            </li>
                        @endforeach
                    </ul>

                    @if($isCurrent)
                        <span class="block text-center bg-ink-100/40 text-ink-500 font-bold py-3 rounded-lg text-sm">
                            Plan actuel
                        </span>
                    @elseif($selectedPlanId === $plan->id)
                        <div class="space-y-3">
                            @if((float) $plan->price > 0)
                                <x-payment-methods wire:model="paymentMethod" name="subscription_payment_method" compact />
                            @endif
                            <button wire:click="choosePlan({{ $plan->id }})" wire:loading.attr="disabled" wire:target="choosePlan({{ $plan->id }})"
                                    class="w-full bg-ink-900 hover:bg-ink-700 text-cream-50 font-bold py-3 rounded-lg transition text-sm disabled:opacity-60">
                                Confirmer {{ (float) $plan->price > 0 ? 'et payer' : '' }}
                            </button>
                        </div>
                    @else
                        <button wire:click="selectPlan({{ $plan->id }})"
                                class="w-full border-2 border-ink-900 text-ink-900 hover:bg-terracotta-50 font-bold py-3 rounded-lg transition text-sm">
                            Choisir ce plan
                        </button>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
