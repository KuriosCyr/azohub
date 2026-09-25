<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'regex:/^[^@\s]+@[^@\s]+\.[^@\s]+$/', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9\s.-]{8,20}$/'],
            'city' => ['required', 'string', 'max:100'],
            'avatar' => ['nullable', 'image', 'max:2048'], // 2MB max
        ];

        // Champs spécifiques aux prestataires
        if ($this->user()->isPrestataire()) {
            $rules['bio'] = ['nullable', 'string', 'max:500'];
            // 'array' seul ne validait pas le contenu : un tableau de 10 000 chaînes
            // arbitraires passait aussi bien qu'une vraie liste de communes.
            $rules['service_areas'] = ['nullable', 'array', 'max:20'];
            $rules['service_areas.*'] = ['string', Rule::in(\App\Models\Service::communes())];
            $rules['languages'] = ['nullable', 'array', 'max:10'];
            $rules['languages.*'] = ['string', 'max:50'];
            $rules['availability'] = ['nullable', 'in:disponible,occupe,indisponible'];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom est obligatoire.',
            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.email' => 'L\'adresse e-mail doit être valide.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'phone.required' => 'Le numéro de téléphone est obligatoire.',
            'phone.regex' => 'Le numéro de téléphone n\'est pas valide.',
            'city.required' => 'La ville est obligatoire.',
            'avatar.image' => 'Le fichier doit être une image.',
            'avatar.max' => 'L\'image ne doit pas dépasser 2 MB.',
            'bio.max' => 'La biographie ne doit pas dépasser 500 caractères.',
        ];
    }
}