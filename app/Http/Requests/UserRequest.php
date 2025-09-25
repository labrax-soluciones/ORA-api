<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $method = $this->method();

        $requiredFields = ['name', 'email', 'password', 'password_confirmation'];

        $baseRules = [
            'name' => ['string', 'max:255'],
            'email' => ['string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['string', 'min:8', 'confirmed'],
            'password_confirmation' => ['string', 'min:8'],

            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'id_document' => ['nullable', 'string', 'max:50'],
            'avatar_path' => ['nullable', 'string', 'max:255'],
        ];

        switch ($method) {
            case 'POST':
                foreach ($requiredFields as $field) {
                    $baseRules[$field][] = 'required';
                }
                return $baseRules;
            case 'PUT':
                foreach ($requiredFields as $field) {
                    $baseRules[$field][] = 'required';
                }
                return $baseRules;
            case 'PATCH':
                foreach ($requiredFields as $field) {
                    $baseRules[$field][] = 'sometimes';
                }
                return $baseRules;
        }

        return $baseRules;

    }
}
