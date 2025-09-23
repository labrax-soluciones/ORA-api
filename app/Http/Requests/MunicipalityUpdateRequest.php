<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class MunicipalityUpdateRequest extends FormRequest {
    public function authorize(): bool {
        return true;
    }

    public function rules(): array {
        $allowedLocales = ['es', 'en', 'gl', 'pt', 'fr'];
        $id = $this->route('municipality')->id ?? null;

        return [
            'name'           => ['required', 'string', 'max:150', Rule::unique('municipalities', 'name')->ignore($id)],
            'slug'           => ['required', 'string', 'max:150', 'alpha_dash:ascii', Rule::unique('municipalities', 'slug')->ignore($id)],
            'timezone'       => ['required', 'timezone:all'],
            'default_locale' => ['required', Rule::in($allowedLocales)],
            'locales'        => ['required', 'array', 'min:1'],
            'locales.*'      => [Rule::in($allowedLocales)],
            'sso_domains'    => ['nullable', 'array'],
            'sso_domains.*'  => ['string', 'max:255'],
            'contact_email'  => ['nullable', 'email', 'max:255'],
            'contact_phone'  => ['nullable', 'string', 'max:30'],
            'status'         => ['nullable', Rule::in(['active', 'inactive'])],
            'settings'       => ['nullable', 'array'],
        ];
    }

    protected function prepareForValidation(): void {
        $slug = $this->input('slug') ?: $this->input('name');
        $this->merge(['slug' => Str::slug((string) $slug)]);
    }

    public function withValidator($validator) {
        $validator->after(function ($v) {
            $locales = (array) $this->input('locales', []);
            $default = $this->input('default_locale');
            if ($default && !in_array($default, $locales, true)) {
                $v->errors()->add('locales', __('validation.custom.locales.must_include_default'));
            }
        });
    }
}
