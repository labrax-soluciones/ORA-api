<?php

namespace App\Http\Requests\Parking;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreParkingZoneTypeRequest extends FormRequest {
    public function authorize(): bool {
        return true;
    }

    /**
     * Normaliza entrada antes de validar:
     * - name/slug: trim; slug -> kebab-case en minúsculas.
     * - outside_schedule_policy: a minúsculas + mapeo de sinónimos.
     * - color_hex: '' -> null; asegura '#'+6/8 hex y en MAYÚSCULAS.
     * - max_stay_minutes: '' -> null.
     */
    protected function prepareForValidation(): void {
        // name / slug
        $name = is_string($this->input('name')) ? trim($this->input('name')) : $this->input('name');
        $slug = $this->input('slug');
        if (is_string($slug)) {
            $slug = Str::slug($slug); // kebab-case seguro
        }

        // policy con sinónimos (por si el front antiguo envía otros valores)
        $policy = $this->input('outside_schedule_policy');
        if (is_string($policy)) {
            $policy = strtolower(trim($policy));
            $map = [
                'unlimited'        => 'unlimited',
                'forbidden'        => 'forbidden',
                'same_as_inside'   => 'same_as_inside',
                'same'             => 'same_as_inside', // sinónimo aceptado
                'same-as-inside'   => 'same_as_inside', // por si viene con guiones
            ];
            $policy = $map[$policy] ?? $policy;
        }

        // color: normalizar; si viene "" -> null
        $color = $this->input('color_hex');
        if ($color === '') {
            $color = null;
        } elseif (is_string($color)) {
            $color = trim($color);
            // Añadir '#' si falta
            if ($color !== '' && $color[0] !== '#') {
                $color = "#{$color}";
            }
            // Mayúsculas para almacenar homogéneo
            $color = strtoupper($color);
        }

        // max_stay_minutes: '' -> null
        $maxStay = $this->input('max_stay_minutes');
        if ($maxStay === '') {
            $maxStay = null;
        }

        // settings: si viene cadena vacía -> null (el front a veces manda "")
        $settings = $this->input('settings');
        if ($settings === '') {
            $settings = null;
        }

        $this->merge([
            'name'                     => $name,
            'slug'                     => $slug,
            'outside_schedule_policy'  => $policy,
            'color_hex'                => $color,
            'max_stay_minutes'         => $maxStay,
            'settings'                 => $settings,
        ]);
    }

    public function rules(): array {
        return [
            'name' => ['bail', 'required', 'string', 'max:120'],
            'slug' => ['bail', 'required', 'string', 'max:140', 'alpha_dash'],

            // Hex #RRGGBB o #RRGGBBAA
            'color_hex' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{8})$/'],

            'max_stay_minutes' => ['nullable', 'integer', 'min:1'],

            // Asegúrate de que el front use exactamente estos valores
            'outside_schedule_policy' => ['bail', 'required', 'in:unlimited,forbidden,same_as_inside'],

            'status' => ['bail', 'required', 'in:active,inactive'],

            'settings' => ['nullable', 'array'],
        ];
    }

    public function messages(): array {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.string'   => 'El nombre debe ser un texto.',
            'name.max'      => 'El nombre no puede superar los :max caracteres.',

            'slug.required'   => 'El identificador (slug) es obligatorio.',
            'slug.string'     => 'El identificador (slug) debe ser un texto.',
            'slug.max'        => 'El identificador (slug) no puede superar los :max caracteres.',
            'slug.alpha_dash' => 'El identificador (slug) solo puede contener letras, números, guiones y guiones bajos.',

            'color_hex.regex' => 'El color debe tener formato #RRGGBB o #RRGGBBAA.',

            'max_stay_minutes.integer' => 'La estancia máxima debe ser un número entero de minutos.',
            'max_stay_minutes.min'     => 'La estancia máxima debe ser como mínimo :min minuto(s).',

            'outside_schedule_policy.required' => 'La política fuera de horario es obligatoria.',
            'outside_schedule_policy.in'       => 'La política fuera de horario debe ser una de: unlimited, forbidden o same_as_inside.',

            'status.required' => 'El estado es obligatorio.',
            'status.in'       => 'El estado debe ser activo (active) o inactivo (inactive).',

            'settings.array'  => 'Los ajustes deben ser un objeto/array.',
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
