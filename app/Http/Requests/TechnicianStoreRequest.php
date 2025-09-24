<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TechnicianStoreRequest extends FormRequest {
    public function authorize(): bool {
        // La autorización fina la hace el middleware de permisos + scope.
        return true;
    }

    public function rules(): array {
        return [
            // Usuario
            'email'       => ['required', 'email', 'max:255', 'unique:users,email'],
            'first_name'  => ['required', 'string', 'max:100'],
            'last_name'   => ['required', 'string', 'max:100'],
            'phone'       => ['nullable', 'string', 'max:50'],
            'id_document' => ['nullable', 'string', 'max:50'],

            // Perfil de técnico
            'department'  => ['nullable', 'string', 'max:120'],
            'position'    => ['nullable', 'string', 'max:120'],
        ];
    }
}
