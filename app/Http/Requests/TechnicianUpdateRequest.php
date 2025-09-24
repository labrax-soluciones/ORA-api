<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TechnicianUpdateRequest extends FormRequest {
    public function authorize(): bool {
        return true; // permisos y scope los controlan middlewares/ruta
    }

    public function rules(): array {
        $tech = $this->route('technician'); // TechnicianProfile por binding
        $userId = $tech?->user_id;

        return [
            // Usuario
            'email'       => ['required', 'email', 'max:255', "unique:users,email,{$userId}"],
            'first_name'  => ['required', 'string', 'max:100'],
            'last_name'   => ['required', 'string', 'max:100'],
            'phone'       => ['nullable', 'string', 'max:50'],
            'id_document' => ['nullable', 'string', 'max:50'],

            // Perfil técnico
            'department'  => ['nullable', 'string', 'max:120'],
            'position'    => ['nullable', 'string', 'max:120'],
        ];
    }
}
