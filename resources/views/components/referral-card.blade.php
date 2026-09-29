@php
    $user = auth()->user();
    // Le lien pointe toujours vers l'inscription prestataire, quel que soit le rôle du
    // parrain : c'est ce canal que le parrainage cherche à alimenter en priorité, même
    // quand la personne invitée finit par s'inscrire comme cliente (elle peut changer
    // de rôle sur la page d'inscription elle-même).
    $referralLink = route('register', ['role' => 'prestataire', 'ref' => $user->referral_code]);
    $referredCount = $user->referrals()->count();
    $rewardedCount = $user->referrals()->where('referral_reward_granted', true)->count();
@endphp

<div class="bg-cream-50 rounded-xl border border-ink-100 p-6">
    <div class="flex items-center gap-3 mb-4">
        <div class="w-10 h-10 rounded-lg bg-terracotta-50 flex items-center justify-center flex-shrink-0">
            <x-app-icon name="share" class="w-5 h-5 text-terracotta-600" />
        </div>
        <div>
            <h3 class="font-bold text-ink-900">Parrainez et gagnez</h3>
            <p class="text-sm text-ink-500">100 FCFA de crédit par filleul, dès sa première commande</p>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <input type="text"
               readonly
               value="{{ $referralLink }}"
               onclick="this.select()"
               id="referral-link-input"
               aria-label="Votre lien de parrainage"
               class="flex-1 px-4 py-2.5 border-2 border-ink-100 rounded-lg text-sm text-ink-700 bg-white truncate">
        <button type="button"
                onclick="navigator.clipboard.writeText(document.getElementById('referral-link-input').value).then(() => { const btn = document.getElementById('referral-copy-btn'); const original = btn.textContent; btn.textContent = 'Copié !'; setTimeout(() => btn.textContent = original, 2000); })"
                id="referral-copy-btn"
                class="px-5 py-2.5 bg-terracotta-600 hover:bg-terracotta-700 text-cream-50 font-bold rounded-lg transition text-sm whitespace-nowrap">
            Copier le lien
        </button>
    </div>

    <div class="grid grid-cols-3 gap-3 text-center">
        <div class="bg-white rounded-lg border border-ink-100 py-3">
            <div class="text-xl font-bold text-ink-900">{{ $referredCount }}</div>
            <div class="text-xs text-ink-500 mt-0.5">Filleuls inscrits</div>
        </div>
        <div class="bg-white rounded-lg border border-ink-100 py-3">
            <div class="text-xl font-bold text-ink-900">{{ $rewardedCount }}</div>
            <div class="text-xs text-ink-500 mt-0.5">Ont rapporté</div>
        </div>
        <div class="bg-white rounded-lg border border-ink-100 py-3">
            <div class="text-xl font-bold text-terracotta-600">{{ number_format($user->referral_credit_balance, 0, ',', ' ') }}</div>
            <div class="text-xs text-ink-500 mt-0.5">FCFA de crédit</div>
        </div>
    </div>

    <p class="text-xs text-ink-400 mt-3">
        @if($user->isPrestataire())
            Ce crédit n'est pas retirable. Cochez « Utiliser mon crédit de parrainage » au moment de payer votre abonnement (Pro ou Premium) pour l'appliquer en réduction.
        @else
            Ce crédit n'est pas retirable. Cochez « Utiliser mon crédit de parrainage » au moment de payer une commande pour l'appliquer en réduction.
        @endif
    </p>
</div>
