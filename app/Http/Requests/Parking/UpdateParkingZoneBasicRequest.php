<?php

namespace App\Http\Requests\Parking;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class UpdateParkingZoneBasicRequest extends FormRequest {
    public function authorize(): bool {
        return true;
    }

    protected function prepareForValidation(): void {
        if ($this->has('slug') && is_string($this->input('slug'))) {
            $this->merge(['slug' => Str::slug($this->input('slug'))]);
        }
        if ($this->has('name') && is_string($this->input('name'))) {
            $this->merge(['name' => trim($this->input('name'))]);
        }
        if ($this->has('status') && is_string($this->input('status'))) {
            $this->merge(['status' => strtolower(trim($this->input('status')))]);
        }

        if ($this->has('description') && $this->input('description') === '') $this->merge(['description' => null]);
        if ($this->has('capacity') && $this->input('capacity') === '')       $this->merge(['capacity' => null]);
        if ($this->has('metadata') && $this->input('metadata') === '')       $this->merge(['metadata' => null]);
    }

    public function rules(): array {
        return [
            'parking_zone_type_id' => ['bail', 'sometimes', 'integer', 'exists:parking_zone_types,id'],
            'name'        => ['bail', 'sometimes', 'string', 'max:160'],
            'slug'        => ['bail', 'sometimes', 'string', 'max:180', 'alpha_dash'],
            'description' => ['sometimes', 'nullable', 'string'],
            'capacity'    => ['sometimes', 'nullable', 'integer', 'min:1'],
            'status'      => ['bail', 'sometimes', 'in:active,inactive,draft'],
            // geometry NO se toca aquí
            'metadata'    => ['sometimes', 'nullable', 'array'],
        ];
    }

    public function messages(): array {
        return [
            'parking_zone_type_id.integer' => 'El tipo de zona debe ser un identificador válido.',
            'parking_zone_type_id.exists'  => 'El tipo de zona seleccionado no existe.',
            'name.string' => 'El nombre debe ser un texto.',
            'name.max'    => 'El nombre no puede superar los :max caracteres.',
            'slug.string'     => 'El identificador (slug) debe ser un texto.',
            'slug.max'        => 'El identificador (slug) no puede superar los :max caracteres.',
            'slug.alpha_dash' => 'El identificador (slug) solo puede contener letras, números, guiones y guiones bajos.',
            'capacity.integer' => 'La capacidad debe ser un número entero.',
            'capacity.min'     => 'La capacidad mínima es :min.',
            'status.in' => 'El estado debe ser uno de: active, inactive o draft.',
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
