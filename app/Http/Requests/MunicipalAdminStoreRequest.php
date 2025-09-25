<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MunicipalAdminStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ya controlado por middleware/permissions
    }

    public function rules(): array
    {
        return [
            'email'       => ['required', 'email', 'max:190', 'unique:users,email'],
            'first_name'  => ['required', 'string', 'max:120'],
            'last_name'   => ['required', 'string', 'max:120'],
            'phone'       => ['nullable', 'string', 'max:50'],
            'id_document' => ['nullable', 'string', 'max:120'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Este email ya está en uso.',
        ];
    }
}
