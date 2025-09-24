<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PoliceStoreRequest extends FormRequest {
    public function authorize(): bool {
        return true;
    }

    public function rules(): array {
        return [
            // User
            'email'       => ['required', 'email', 'max:255', 'unique:users,email'],
            'first_name'  => ['required', 'string', 'max:100'],
            'last_name'   => ['required', 'string', 'max:100'],
            'phone'       => ['nullable', 'string', 'max:50'],
            'id_document' => ['nullable', 'string', 'max:50'],

            // Police profile
            'badge_number' => ['required', 'string', 'max:100', 'unique:police_profiles,badge_number'],
            'rank'         => ['nullable', 'string', 'max:100'],
        ];
    }
}
