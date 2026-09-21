@props([
    'methods' => ['mtn_momo', 'moov_money', 'celtiis_cash', 'card'],
    'name' => 'payment_method',
    'selected' => null,
    'compact' => false,
    'cols' => 'grid-cols-2',
])

{{-- Choix EXCLUSIF d'un moyen de paiement.
     Tous les boutons radio partagent le même attribut name : sans lui, le navigateur ne les
     groupe pas et plusieurs cartes pouvaient apparaître sélectionnées en même temps.
     Les attributs wire:* (ex. wire:model="paymentMethod") sont transmis à chaque radio. --}}
@php
    $labels = [
        'mtn_momo' => ['MTN MoMo', 'Mobile Money'],
        'moov_money' => ['Moov Money', 'Mobile Money'],
        'celtiis_cash' => ['Celtiis Cash', 'Mobile Money'],
        'card' => ['Carte bancaire', 'Visa · Mastercard'],
    ];
@endphp

<div class="grid {{ $cols }} {{ $compact ? 'gap-2' : 'gap-3' }}" role="radiogroup">
    @foreach($methods as $method)
        <label class="relative block cursor-pointer">
            <input type="radio"
                   name="{{ $name }}"
                   value="{{ $method }}"
                   class="peer sr-only"
                   @checked($selected === $method)
                   {{ $attributes->whereStartsWith('wire:') }}>

            <div class="flex items-center {{ $compact ? 'gap-2 p-2' : 'gap-3 p-3' }} rounded-xl border-2 border-ink-200 bg-cream-50 transition hover:border-terracotta-600/40 peer-checked:border-terracotta-600 peer-checked:bg-terracotta-50 peer-focus-visible:ring-2 peer-focus-visible:ring-terracotta-600/40">
                <x-payment-logo :method="$method" class="{{ $compact ? 'h-7 w-11' : 'h-9 w-14' }} flex-shrink-0" />
                <div class="min-w-0">
                    <p class="{{ $compact ? 'text-xs' : 'text-sm' }} font-semibold leading-tight text-ink-900">{{ $labels[$method][0] ?? $method }}</p>
                    @unless($compact)
                        <p class="mt-0.5 text-xs text-ink-400">{{ $labels[$method][1] ?? '' }}</p>
                    @endunless
                </div>
            </div>

            <span class="pointer-events-none absolute right-2 top-2 hidden h-5 w-5 items-center justify-center rounded-full bg-terracotta-600 text-cream-50 peer-checked:flex">
                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </span>
        </label>
    @endforeach
</div>
