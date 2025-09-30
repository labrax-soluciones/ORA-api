<?php

namespace App\Http\Requests;

use App\Enums\MunicipalityStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class MunicipalityStoreRequest extends FormRequest {
    public function authorize(): bool {
        // Autorización fina por middleware/roles
        return true;
    }

    public function rules(): array {
        $status = array_map(fn($c) => $c->value, MunicipalityStatus::cases());

        return [
            'name'           => ['required', 'string', 'max:150', 'unique:municipalities,name'],
            // slug opcional; si viene, se valida; si no, lo generamos en prepareForValidation
            'slug'           => ['nullable', 'string', 'max:150', 'alpha_dash:ascii', 'unique:municipalities,slug'],
            'contact_email'  => ['nullable', 'email', 'max:255'],
            'contact_phone'  => ['nullable', 'string', 'max:30'],
            'status'         => ['nullable', Rule::in($status)],  // default active
            'settings'       => ['nullable', 'array'],
        ];
    }

    protected function prepareForValidation(): void {
        // Si no envían slug, lo generamos desde name
        $slug = $this->input('slug') ?: $this->input('name');
        $this->merge([
            'slug'   => $slug ? Str::slug((string) $slug) : null,
            'status' => $this->input('status', 'active'),
        ]);
    }

    public function attributes(): array {
        return [
            'name'          => 'nombre',
            'slug'          => 'identificador',
            'contact_email' => 'email de contacto',
            'contact_phone' => 'teléfono de contacto',
            'status'        => 'estado',
            'settings'      => 'ajustes',
        ];
    }

    public function messages(): array {
        return [
            'slug.alpha_dash' => 'El :attribute solo puede contener letras, números y guiones.',
        ];
    }
}
