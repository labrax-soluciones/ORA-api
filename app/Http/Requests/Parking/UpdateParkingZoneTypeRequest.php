<?php

namespace App\Http\Requests\Parking;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class UpdateParkingZoneTypeRequest extends FormRequest {
    public function authorize(): bool {
        return true;
    }

    /**
     * Normaliza antes de validar:
     * - name/slug: trim; slug -> kebab-case.
     * - color_hex: '' -> null, añade '#' si falta, mayúsculas.
     * - max_stay_minutes: '' -> null.
     * - outside_schedule_policy: minúsculas + sinónimos.
     * - status: minúsculas.
     * - settings: '' -> null.
     */
    protected function prepareForValidation(): void {
        // name / slug
        if ($this->has('name') && is_string($this->input('name'))) {
            $this->merge(['name' => trim($this->input('name'))]);
        }
        if ($this->has('slug') && is_string($this->input('slug'))) {
            $this->merge(['slug' => Str::slug($this->input('slug'))]);
        }

        // policy
        if ($this->has('outside_schedule_policy') && is_string($this->input('outside_schedule_policy'))) {
            $policy = strtolower(trim($this->input('outside_schedule_policy')));
            $map = [
                'unlimited'       => 'unlimited',
                'forbidden'       => 'forbidden',
                'same_as_inside'  => 'same_as_inside',
                'same'            => 'same_as_inside',
                'same-as-inside'  => 'same_as_inside',
            ];
            $this->merge(['outside_schedule_policy' => $map[$policy] ?? $policy]);
        }

        // color
        if ($this->has('color_hex')) {
            $color = $this->input('color_hex');
            if ($color === '') {
                $color = null;
            } elseif (is_string($color)) {
                $color = trim($color);
                if ($color !== '' && $color[0] !== '#') {
                    $color = "#{$color}";
                }
                $color = strtoupper($color);
            }
            $this->merge(['color_hex' => $color]);
        }

        // max_stay_minutes
        if ($this->has('max_stay_minutes') && $this->input('max_stay_minutes') === '') {
            $this->merge(['max_stay_minutes' => null]);
        }

        // status
        if ($this->has('status') && is_string($this->input('status'))) {
            $this->merge(['status' => strtolower(trim($this->input('status')))]);
        }

        // settings
        if ($this->has('settings') && $this->input('settings') === '') {
            $this->merge(['settings' => null]);
        }
    }

    public function rules(): array {
        return [
            'name'                    => ['bail', 'sometimes', 'string', 'max:120'],
            'slug'                    => ['bail', 'sometimes', 'string', 'max:140', 'alpha_dash'],

            // Hex #RRGGBB o #RRGGBBAA
            'color_hex'               => ['sometimes', 'nullable', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{8})$/'],

            'max_stay_minutes'        => ['sometimes', 'nullable', 'integer', 'min:1'],

            'outside_schedule_policy' => ['bail', 'sometimes', 'in:unlimited,forbidden,same_as_inside'],

            'status'                  => ['bail', 'sometimes', 'in:active,inactive'],

            'settings'                => ['sometimes', 'nullable', 'array'],
        ];
    }

    public function messages(): array {
        return [
            'name.string'  => 'El nombre debe ser un texto.',
            'name.max'     => 'El nombre no puede superar los :max caracteres.',

            'slug.string'     => 'El identificador (slug) debe ser un texto.',
            'slug.max'        => 'El identificador (slug) no puede superar los :max caracteres.',
            'slug.alpha_dash' => 'El identificador (slug) solo puede contener letras, números, guiones y guiones bajos.',

            'color_hex.regex' => 'El color debe tener formato #RRGGBB o #RRGGBBAA.',

            'max_stay_minutes.integer' => 'La estancia máxima debe ser un número entero de minutos.',
            'max_stay_minutes.min'     => 'La estancia máxima debe ser como mínimo :min minuto(s).',

            'outside_schedule_policy.in' => 'La política fuera de horario debe ser una de: unlimited, forbidden o same_as_inside.',

            'status.in' => 'El estado debe ser activo (active) o inactivo (inactive).',

            'settings.array' => 'Los ajustes deben ser un objeto/array.',
        ];
    }

    public function attributes(): array {
        return [
            'name'                    => 'nombre',
            'slug'                    => 'identificador (slug)',
            'color_hex'               => 'color',
            'max_stay_minutes'        => 'estancia máxima (minutos)',
            'outside_schedule_policy' => 'política fuera de horario',
            'status'                  => 'estado',
            'settings'                => 'ajustes',
        ];
    }
}
