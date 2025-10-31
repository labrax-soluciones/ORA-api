<?php

namespace App\Http\Requests\Me;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMyProfileRequest extends FormRequest {
    public function authorize(): bool {
        return true; // autenticación ya garantizada por el middleware
    }

    protected function prepareForValidation(): void {
        // Normalizamos strings vacíos a null
        $keys = [
            'user.phone',
            'user.id_document',
            'profile.address_line1',
            'profile.address_line2',
            'profile.city',
            'profile.province',
            'profile.postal_code',
            'profile.country',
            'profile.secondary_phone',
        ];
        foreach ($keys as $key) {
            if ($this->filled($key) && $this->input($key) === '') {
                data_set($this->request, $key, null);
            }
        }
    }

    public function rules(): array {
        $userId = (int) optional($this->user())->id;

        return [
            'user' => ['nullable', 'array'],
            'user.email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'user.first_name'  => ['nullable', 'string', 'max:100'],
            'user.last_name'   => ['nullable', 'string', 'max:100'],
            'user.phone'       => ['nullable', 'string', 'max:50'],
            'user.id_document' => ['nullable', 'string', 'max:50'],

            'profile' => ['nullable', 'array'],
            'profile.address_line1'   => ['nullable', 'string', 'max:160'],
            'profile.address_line2'   => ['nullable', 'string', 'max:160'],
            'profile.city'            => ['nullable', 'string', 'max:120'],
            'profile.province'        => ['nullable', 'string', 'max:120'],
            'profile.postal_code'     => ['nullable', 'string', 'max:20'],
            'profile.country'         => ['nullable', 'string', 'size:2'],
            'profile.date_of_birth'   => ['nullable', 'date'],
            'profile.secondary_phone' => ['nullable', 'string', 'max:50'],
            'profile.meta'            => ['nullable', 'array'],
        ];
    }

    public function messages(): array {
        return [
            'user.email.email'   => 'El correo no tiene un formato válido.',
            'user.email.unique'  => 'Este correo ya está en uso.',
            'user.first_name.max' => 'El nombre no puede superar los :max caracteres.',
            'user.last_name.max' => 'Los apellidos no pueden superar los :max caracteres.',
            'profile.country.size' => 'El país debe ser un código ISO-3166-1 alfa-2 (por ejemplo, ES).',
            'profile.date_of_birth.date' => 'La fecha de nacimiento no es válida.',
        ];
    }

    public function attributes(): array {
        return [
            'user.email' => 'correo electrónico',
            'user.first_name' => 'nombre',
            'user.last_name'  => 'apellidos',
            'user.phone'      => 'teléfono',
            'user.id_document' => 'documento de identidad',
            'profile.address_line1' => 'dirección (línea 1)',
            'profile.address_line2' => 'dirección (línea 2)',
            'profile.city'          => 'ciudad',
            'profile.province'      => 'provincia',
            'profile.postal_code'   => 'código postal',
            'profile.country'       => 'país',
            'profile.date_of_birth' => 'fecha de nacimiento',
            'profile.secondary_phone' => 'teléfono secundario',
        ];
    }
}
