<?php

namespace App\Http\Requests\Parking;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreParkingZoneBasicRequest extends FormRequest {
    public function authorize(): bool {
        return true;
    }

    protected function prepareForValidation(): void {
        if (is_string($this->input('slug'))) {
            $this->merge(['slug' => Str::slug($this->input('slug'))]);
        }
        if (is_string($this->input('name'))) {
            $this->merge(['name' => trim($this->input('name'))]);
        }
        if ($this->input('description') === '') $this->merge(['description' => null]);
        if ($this->input('capacity') === '') $this->merge(['capacity' => null]);
        if ($this->input('metadata') === '') $this->merge(['metadata' => null]);
        if (is_string($this->input('status'))) {
            $this->merge(['status' => strtolower(trim($this->input('status')))]);
        }
    }

    public function rules(): array {
        return [
            'parking_zone_type_id' => ['bail', 'required', 'integer', 'exists:parking_zone_types,id'],
            'name'                 => ['bail', 'required', 'string', 'max:160'],
            'slug'                 => ['bail', 'required', 'string', 'max:180', 'alpha_dash'],
            'description'          => ['nullable', 'string'],
            'capacity'             => ['nullable', 'integer', 'min:1'],
            'status'               => ['bail', 'required', 'in:active,inactive,draft'],
            // geometry NO va aquí (siempre null al crear básicos)
            'metadata'             => ['nullable', 'array'],
        ];
    }

    public function messages(): array {
        return [
            'parking_zone_type_id.required' => 'El tipo de zona es obligatorio.',
            'parking_zone_type_id.integer'  => 'El tipo de zona debe ser un identificador válido.',
            'parking_zone_type_id.exists'   => 'El tipo de zona seleccionado no existe.',
            'name.required' => 'El nombre es obligatorio.',
            'name.string'   => 'El nombre debe ser un texto.',
            'name.max'      => 'El nombre no puede superar los :max caracteres.',
            'slug.required'   => 'El identificador (slug) es obligatorio.',
            'slug.string'     => 'El identificador (slug) debe ser un texto.',
            'slug.max'        => 'El identificador (slug) no puede superar los :max caracteres.',
            'slug.alpha_dash' => 'El identificador (slug) solo puede contener letras, números, guiones y guiones bajos.',
            'capacity.integer' => 'La capacidad debe ser un número entero.',
            'capacity.min'     => 'La capacidad mínima es :min.',
            'status.required' => 'El estado es obligatorio.',
            'status.in'       => 'El estado debe ser uno de: active, inactive o draft.',
            'metadata.array' => 'Los metadatos deben ser un objeto/array.',
        ];
    }

    public function attributes(): array {
        return [
            'parking_zone_type_id' => 'tipo de zona',
            'name'                 => 'nombre',
            'slug'                 => 'identificador (slug)',
            'description'          => 'descripción',
            'capacity'             => 'capacidad',
            'status'               => 'estado',
            'metadata'             => 'metadatos',
        ];
    }
}
