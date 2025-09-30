<?php

namespace App\Http\Requests;

use App\Enums\MunicipalityStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class MunicipalityUpdateRequest extends FormRequest {
    public function authorize(): bool {
        return true;
    }

    public function rules(): array {
        $status = array_map(fn($c) => $c->value, MunicipalityStatus::cases());
        $id = $this->route('municipality')->id ?? null;

        return [
            // En update permitimos parches: usa "sometimes" + "filled"
            'name'           => ['sometimes', 'filled', 'string', 'max:150', Rule::unique('municipalities', 'name')->ignore($id)],
            'slug'           => ['sometimes', 'nullable', 'string', 'max:150', 'alpha_dash:ascii', Rule::unique('municipalities', 'slug')->ignore($id)],
            'contact_email'  => ['sometimes', 'nullable', 'email', 'max:255'],
            'contact_phone'  => ['sometimes', 'nullable', 'string', 'max:30'],
            'status'         => ['sometimes', 'nullable', Rule::in($status)],
            'settings'       => ['sometimes', 'nullable', 'array'],
        ];
    }

    protected function prepareForValidation(): void {
        // Si mandan slug vacío pero hay name, lo normalizamos; si no mandan slug, no tocamos
        if ($this->has('slug')) {
            $slug = $this->input('slug');
            $slug = $slug ?: $this->input('name'); // si viene vacío, lo generamos desde name
            $this->merge(['slug' => $slug ? Str::slug((string) $slug) : null]);
        }
    }
}
