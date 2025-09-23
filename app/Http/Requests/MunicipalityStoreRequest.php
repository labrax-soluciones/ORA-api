<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class MunicipalityStoreRequest extends FormRequest {
    public function authorize(): bool {
        // Autorización fina la controla el middleware de ruta (rol admin).
        return true;
    }

    public function rules(): array {
        $allowedLocales = ['es', 'en', 'gl', 'pt', 'fr'];

        return [
            'name'           => ['required', 'string', 'max:150', 'unique:municipalities,name'],
            'slug'           => ['required', 'string', 'max:150', 'alpha_dash:ascii', 'unique:municipalities,slug'],
            'timezone'       => ['required', 'timezone:all'],
            'default_locale' => ['required', Rule::in($allowedLocales)],
            'locales'        => ['required', 'array', 'min:1'],
            'locales.*'      => [Rule::in($allowedLocales)],
            'sso_domains'    => ['nullable', 'array'],
            'sso_domains.*'  => ['string', 'max:255'], // valida FQDN simple
            'contact_email'  => ['nullable', 'email', 'max:255'],
            'contact_phone'  => ['nullable', 'string', 'max:30'],
            'status'         => ['nullable', Rule::in(['active', 'inactive'])],
            'settings'       => ['nullable', 'array'],
        ];
    }

    // Normaliza el slug a partir del name si no viene, y asegura formato
    protected function prepareForValidation(): void {
        $slug = $this->input('slug') ?: $this->input('name');

        $this->merge([
            'slug' => Str::slug((string) $slug),
            'status' => $this->input('status', 'active'),
        ]);
    }

    // Regla adicional: el default_locale debe incluirse en locales
    public function withValidator($validator) {
        $validator->after(function ($v) {
            $locales = (array) $this->input('locales', []);
            $default = $this->input('default_locale');
            if ($default && !in_array($default, $locales, true)) {
                $v->errors()->add('locales', __('validation.custom.locales.must_include_default'));
            }
        });
    }

    // Etiquetas y mensajes en castellano
    public function attributes(): array {
        return [
            'name'           => 'nombre',
            'slug'           => 'identificador',
            'timezone'       => 'zona horaria',
            'default_locale' => 'idioma por defecto',
            'locales'        => 'idiomas habilitados',
            'sso_domains'    => 'dominios SSO',
            'contact_email'  => 'email de contacto',
            'contact_phone'  => 'teléfono de contacto',
            'status'         => 'estado',
        ];
    }

    public function messages(): array {
        return [
            'slug.alpha_dash' => 'El :attribute solo puede contener letras, números y guiones.',
            'locales.*.in'    => 'Idioma no permitido. Use: es, en, gl, pt o fr.',
        ];
    }
}
