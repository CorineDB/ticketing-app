<?php

namespace App\Http\Requests\Api\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class CreateUserRequest extends FormRequest
{
    /**
     * Est-ce que l'utilisateur est autorisé à faire cette requête ?
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Avant la validation : nettoyer les données, normaliser, caster.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge([
                'email' => strtolower(trim($this->email)),
            ]);
        }

        if ($this->has('name')) {
            $this->merge([
                'name' => trim($this->name),
            ]);
        }
    }

    /**
     * Règles de validation.
     */
    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ];
    }

    /**
     * Messages d’erreur personnalisés.
     */
    public function messages(): array
    {
        return [
            'name.required'     => 'Le nom est obligatoire.',
            'email.required'    => 'L’email est obligatoire.',
            'email.email'       => 'Le format email est invalide.',
            'password.required' => 'Le mot de passe est obligatoire.',
            '*.string'          => 'Ce champ doit être une chaîne de caractères.',
        ];
    }

    /**
     * Alias lisibles pour les erreurs.
     */
    public function attributes(): array
    {
        return [
            'name'     => 'nom complet',
            'email'    => 'adresse email',
            'password' => 'mot de passe',
        ];
    }

    /**
     * Hook après la validation (si besoin de validations custom).
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->any()) {
                Log::warning("Validation failed in CreateUserRequest", [
                    'errors' => $validator->errors()->toArray()
                ]);
            }
        });
    }

    /**
     * Réponse JSON en cas d'erreurs.
     */
    protected function failedValidation(Validator $validator)
    {
        Log::error('Validation exception in CreateUserRequest', [
            'errors' => $validator->errors()->all()
        ]);

        throw new ValidationException($validator, response()->json([
            'message' => 'Invalid data provided',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
