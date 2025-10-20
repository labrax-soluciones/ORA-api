<?php

namespace App\Http\Requests\Parking;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class UpdateParkingZoneRequest extends FormRequest {
    public function authorize(): bool {
        return true;
    }

    /**
     * Normaliza antes de validar:
     * - slug a kebab-case (minúsculas)
     * - trim en strings
     * - '' -> null en description/capacity/metadata
     * - status a minúsculas
     * - geometry: limitar a keys conocidas
     */
    protected function prepareForValidation(): void {
        $slug = $this->input('slug');
        if ($this->has('slug') && is_string($slug)) {
            $slug = Str::slug($slug);
        }

        $name = $this->input('name');
        if ($this->has('name') && is_string($name)) {
            $name = trim($name);
        }

        $status = $this->input('status');
        if ($this->has('status') && is_string($status)) {
            $status = strtolower(trim($status));
        }

        $description = $this->input('description');
        $capacity    = $this->input('capacity');
        $metadata    = $this->input('metadata');

        $geometry = $this->input('geometry');
        if ($this->has('geometry') && is_array($geometry)) {
            $geometry = [
                'type'        => Arr::get($geometry, 'type'),
                'coordinates' => Arr::get($geometry, 'coordinates'),
            ];
        }

        $this->merge([
            'slug'        => $this->has('slug') ? $slug : $this->input('slug'),
            'name'        => $this->has('name') ? $name : $this->input('name'),
            'status'      => $this->has('status') ? $status : $this->input('status'),

            'description' => $this->has('description')
                ? ($description === '' ? null : $description)
                : $this->input('description'),

            'capacity'    => $this->has('capacity')
                ? ($capacity === '' ? null : $capacity)
                : $this->input('capacity'),

            'metadata'    => $this->has('metadata')
                ? ($metadata === '' ? null : $metadata)
                : $this->input('metadata'),

            'geometry'    => $this->has('geometry') ? $geometry : $this->input('geometry'),
        ]);
    }

    public function rules(): array {
        return [
            'parking_zone_type_id' => ['bail', 'sometimes', 'integer', 'exists:parking_zone_types,id'],

            'name'        => ['bail', 'sometimes', 'string', 'max:160'],
            'slug'        => ['bail', 'sometimes', 'string', 'max:180', 'alpha_dash'],
            'description' => ['sometimes', 'nullable', 'string'],
            'capacity'    => ['sometimes', 'nullable', 'integer', 'min:1'],
            'status'      => ['bail', 'sometimes', 'in:active,inactive,draft'],

            // GeoJSON parcial: si llega geometry, validamos tipo y/o coords.
            'geometry'                 => ['bail', 'sometimes', 'array'],
            'geometry.type'            => ['bail', 'sometimes', 'in:Polygon,MultiPolygon'],
            'geometry.coordinates'     => ['bail', 'sometimes', 'array', 'min:1'],

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

            'description.string' => 'La descripción debe ser un texto.',

            'capacity.integer' => 'La capacidad debe ser un número entero.',
            'capacity.min'     => 'La capacidad mínima es :min.',

            'status.in' => 'El estado debe ser uno de: active, inactive o draft.',

            'geometry.array'                => 'La geometría debe ser un objeto/array válido.',
            'geometry.type.in'              => 'El tipo de geometría debe ser Polygon o MultiPolygon.',
            'geometry.coordinates.array'    => 'Las coordenadas de la geometría deben ser un array.',
            'geometry.coordinates.min'      => 'La geometría debe contener al menos un polígono válido.',

            'metadata.array' => 'Los metadatos deben ser un objeto/array.',
        ];
    }

    public function attributes(): array {
        return [
            'parking_zone_type_id'     => 'tipo de zona',
            'name'                     => 'nombre',
            'slug'                     => 'identificador (slug)',
            'description'              => 'descripción',
            'capacity'                 => 'capacidad',
            'status'                   => 'estado',
            'geometry'                 => 'geometría',
            'geometry.type'            => 'tipo de geometría',
            'geometry.coordinates'     => 'coordenadas de la geometría',
            'metadata'                 => 'metadatos',
        ];
    }
}
