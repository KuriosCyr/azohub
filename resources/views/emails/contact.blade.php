<x-mail::message>
# Nouveau message de contact

**De :** {{ $name }} ({{ $email }})
**Sujet :** {{ $subject }}

{{ $message }}

<x-mail::button :url="'mailto:' . $email">
Répondre à {{ $name }}
</x-mail::button>

Merci,<br>
{{ config('app.name') }}
</x-mail::message>
