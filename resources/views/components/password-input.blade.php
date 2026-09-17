@props(['id', 'name', 'placeholder' => null, 'autocomplete' => null, 'value' => null])

<div x-data="{ show: false }" class="relative">
    <input
        {{ $attributes->merge([
            'id' => $id,
            'name' => $name,
            'placeholder' => $placeholder,
            'autocomplete' => $autocomplete,
            'class' => 'w-full px-4 py-3 pr-12 border-2 border-ink-100 rounded-lg focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition',
        ]) }}
        :type="show ? 'text' : 'password'"
        value="{{ $value }}"
    >
    <button type="button"
            @click="show = !show"
            tabindex="-1"
            class="absolute inset-y-0 right-0 flex items-center px-4 text-ink-300 hover:text-ink-600 transition"
            :aria-label="show ? 'Cacher le mot de passe' : 'Afficher le mot de passe'">
        <x-app-icon name="eye" class="w-5 h-5" x-show="!show" />
        <x-app-icon name="eye-slash" class="w-5 h-5" x-show="show" style="display:none;" />
    </button>
</div>
