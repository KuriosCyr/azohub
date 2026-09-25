@props([
    'passwordId' => 'password',
    'passwordName' => 'password',
    'confirmId' => 'password_confirmation',
    'confirmName' => 'password_confirmation',
    'passwordErrors' => null,
    'confirmErrors' => null,
])

{{--
    Champ "nouveau mot de passe" + "confirmation", avec indicateur de force coloré et
    vérification en direct que les deux correspondent, avant même l'envoi du formulaire.
    La vérification qui compte reste côté serveur (règle 'confirmed' + Password::defaults()) :
    ceci est juste un retour visuel immédiat pour l'utilisateur.
--}}
<div x-data="{
    password: '',
    confirmation: '',
    show: false,
    showConfirm: false,
    get score() {
        let s = 0;
        if (this.password.length >= 8) s++;
        if (this.password.length >= 12) s++;
        if (/[a-z]/.test(this.password) && /[A-Z]/.test(this.password)) s++;
        if (/[0-9]/.test(this.password)) s++;
        if (/[^a-zA-Z0-9]/.test(this.password)) s++;
        return s;
    },
    get label() {
        if (this.password.length === 0) return '';
        if (this.score <= 2) return 'Faible';
        if (this.score === 3) return 'Moyen';
        return 'Fort';
    },
    get barColor() {
        if (this.score <= 2) return 'bg-red-500';
        if (this.score === 3) return 'bg-ochre-500';
        return 'bg-forest-600';
    },
    get textColor() {
        if (this.score <= 2) return 'text-red-600';
        if (this.score === 3) return 'text-ochre-600';
        return 'text-forest-700';
    },
    get hasMinLength() { return this.password.length >= 8; },
    get hasMixedCase() { return /[a-z]/.test(this.password) && /[A-Z]/.test(this.password); },
    get hasNumber() { return /[0-9]/.test(this.password); },
    get matches() {
        return this.confirmation.length > 0 && this.password === this.confirmation;
    },
    get mismatches() {
        return this.confirmation.length > 0 && this.password !== this.confirmation;
    },
}" class="space-y-6">
    <div>
        <label for="{{ $passwordId }}" class="block text-sm font-bold text-ink-700 mb-2">
            Nouveau mot de passe <span class="text-red-500">*</span>
        </label>
        <div class="relative">
            <input
                id="{{ $passwordId }}"
                name="{{ $passwordName }}"
                :type="show ? 'text' : 'password'"
                x-model="password"
                required
                autocomplete="new-password"
                placeholder="Min. 8 caractères"
                class="w-full px-4 py-3 pr-12 border-2 border-ink-100 rounded-lg focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition">
            <button type="button" @click="show = !show" tabindex="-1"
                    class="absolute inset-y-0 right-0 flex items-center px-4 text-ink-300 hover:text-ink-600 transition"
                    :aria-label="show ? 'Cacher le mot de passe' : 'Afficher le mot de passe'">
                <x-app-icon name="eye" class="w-5 h-5" x-show="!show" />
                <x-app-icon name="eye-slash" class="w-5 h-5" x-show="show" style="display:none;" />
            </button>
        </div>

        {{-- Indicateur de force --}}
        <div class="mt-2" x-show="password.length > 0" style="display:none;">
            <div class="flex gap-1 h-1.5 rounded-full overflow-hidden bg-ink-100">
                <template x-for="i in 5" :key="i">
                    <div class="flex-1 transition-colors" :class="i <= score ? barColor : 'bg-ink-100'"></div>
                </template>
            </div>
            <p class="text-xs font-semibold mt-1" :class="textColor" x-text="'Force : ' + label"></p>
        </div>

        {{-- Ce que le mot de passe doit contenir --}}
        <ul class="mt-2 space-y-0.5 text-xs">
            <li class="flex items-center gap-1.5" :class="hasMinLength ? 'text-forest-700' : 'text-ink-400'">
                <x-app-icon name="check" class="w-3 h-3 flex-shrink-0" />
                8 caractères minimum
            </li>
            <li class="flex items-center gap-1.5" :class="hasMixedCase ? 'text-forest-700' : 'text-ink-400'">
                <x-app-icon name="check" class="w-3 h-3 flex-shrink-0" />
                Une majuscule et une minuscule
            </li>
            <li class="flex items-center gap-1.5" :class="hasNumber ? 'text-forest-700' : 'text-ink-400'">
                <x-app-icon name="check" class="w-3 h-3 flex-shrink-0" />
                Un chiffre
            </li>
        </ul>

        @if($passwordErrors)
            <x-input-error :messages="$passwordErrors" class="mt-2" />
        @endif
    </div>

    <div>
        <label for="{{ $confirmId }}" class="block text-sm font-bold text-ink-700 mb-2">
            Confirmer le mot de passe <span class="text-red-500">*</span>
        </label>
        <div class="relative">
            <input
                id="{{ $confirmId }}"
                name="{{ $confirmName }}"
                :type="showConfirm ? 'text' : 'password'"
                x-model="confirmation"
                required
                autocomplete="new-password"
                placeholder="Retapez le mot de passe"
                class="w-full px-4 py-3 pr-12 border-2 rounded-lg focus:ring-4 transition"
                :class="mismatches ? 'border-red-400 focus:border-red-500 focus:ring-red-50' : 'border-ink-100 focus:border-terracotta-600 focus:ring-terracotta-50'">
            <button type="button" @click="showConfirm = !showConfirm" tabindex="-1"
                    class="absolute inset-y-0 right-0 flex items-center px-4 text-ink-300 hover:text-ink-600 transition"
                    :aria-label="showConfirm ? 'Cacher le mot de passe' : 'Afficher le mot de passe'">
                <x-app-icon name="eye" class="w-5 h-5" x-show="!showConfirm" />
                <x-app-icon name="eye-slash" class="w-5 h-5" x-show="showConfirm" style="display:none;" />
            </button>
        </div>

        {{-- Correspondance en direct --}}
        <p class="text-xs font-semibold mt-2 text-forest-700" x-show="matches" style="display:none;">
            <x-app-icon name="check" class="w-3.5 h-3.5 inline-block" /> Les mots de passe correspondent
        </p>
        <p class="text-xs font-semibold mt-2 text-red-600" x-show="mismatches" style="display:none;">
            Les mots de passe ne correspondent pas
        </p>

        @if($confirmErrors)
            <x-input-error :messages="$confirmErrors" class="mt-2" />
        @endif
    </div>
</div>
