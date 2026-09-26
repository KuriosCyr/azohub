@props(['messages'])

@if ($messages)
    {{-- data-field-error : permet au script global (resources/js/app.js) de retrouver et de
         faire défiler la page jusqu'à la première erreur visible après une soumission classique
         (rechargement complet, sans Livewire) — sans ça, une erreur sur un champ bas dans un
         long formulaire (ex. l'image de couverture d'un service) passe inaperçue tant qu'on ne
         pense pas à faire défiler soi-même (BUG008 QA). --}}
    <ul data-field-error {{ $attributes->merge(['class' => 'text-sm text-red-600 space-y-1']) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
