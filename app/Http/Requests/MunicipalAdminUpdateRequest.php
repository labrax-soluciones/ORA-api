<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MunicipalAdminUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $adminProfile = $this->route('admin'); // MunicipalAdminProfile
        $userId = $adminProfile?->user_id;

        return [
            'email'       => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($userId)],
            'first_name'  => ['required', 'string', 'max:120'],
            'last_name'   => ['required', 'string', 'max:120'],
            'phone'       => ['nullable', 'string', 'max:50'],
            'id_document' => ['nullable', 'string', 'max:120'],
        ];
    }
}
