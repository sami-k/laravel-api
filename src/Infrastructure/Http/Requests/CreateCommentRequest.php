<?php

namespace Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCommentRequest extends FormRequest
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
            'contenu' => [
                'required',
                'string',
                'min:3',
                'max:1000',
            ],
            'profile_id' => [
                'required',
                'integer',
                'exists:profiles,id',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'contenu.required' => 'Le contenu du commentaire est requis.',
            'contenu.min' => 'Le commentaire doit contenir au moins 3 caractères.',
            'contenu.max' => 'Le commentaire ne peut pas dépasser 1000 caractères.',
            'profile_id.required' => 'L\'ID du profil est requis.',
            'profile_id.integer' => 'L\'ID du profil doit être un nombre entier.',
            'profile_id.exists' => 'Le profil spécifié n\'existe pas.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'contenu' => 'contenu du commentaire',
            'profile_id' => 'profil',
        ];
    }
}
