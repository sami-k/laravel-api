<?php

namespace Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Infrastructure\Eloquent\Profile;

class CreateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // L'authentification est gérée par le middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'nom' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-ZÀ-ÿ\s\-\']+$/'
            ],
            'prenom' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-ZÀ-ÿ\s\-\']+$/'
            ],
            'image' => [
                'nullable',
                'file',
                'image',
                'mimes:jpeg,png,jpg,gif',
                'max:5120' // 5MB max
            ],
            'statut' => [
                'sometimes',
                'string',
                'in:' . implode(',', [
                    Profile::STATUT_INACTIF,
                    Profile::STATUT_EN_ATTENTE,
                    Profile::STATUT_ACTIF
                ])
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom est requis.',
            'nom.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'nom.regex' => 'Le nom ne peut contenir que des lettres, espaces, tirets et apostrophes.',
            'prenom.required' => 'Le prénom est requis.',
            'prenom.max' => 'Le prénom ne peut pas dépasser 255 caractères.',
            'prenom.regex' => 'Le prénom ne peut contenir que des lettres, espaces, tirets et apostrophes.',
            'image.image' => 'Le fichier doit être une image.',
            'image.mimes' => 'L\'image doit être au format JPEG, PNG, JPG ou GIF.',
            'image.max' => 'L\'image ne peut pas dépasser 5MB.',
            'statut.in' => 'Le statut doit être: inactif, en_attente ou actif.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Définir le statut par défaut si non fourni
        if (!$this->has('statut')) {
            $this->merge([
                'statut' => Profile::STATUT_EN_ATTENTE
            ]);
        }
    }
}
