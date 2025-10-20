<?php

namespace App\Http\Requests\Parking;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class StoreParkingZoneRequest extends FormRequest {
    public function authorize(): bool {
        return true;
    }

    /**
     * Normaliza/limpia antes de validar:
     * - slug en kebab-case (minúsculas, guiones)
     * - trim de strings
     * - '' -> null en description/capacity/metadata
     */
    protected function prepareForValidation(): void {
        $name = is_string($this->input('name')) ? trim($this->input('name')) : $this->input('name');
        $slug = $this->input('slug');
        if (is_string($slug)) {
            $slug = Str::slug($slug); // kebab-case seguro
        }

        $description = $this->input('description');
        $capacity = $this->input('capacity');
        $metadata = $this->input('metadata');

        $geometry = $this->input('geometry');
        if (is_array($geometry)) {
            // Limpia keys anidadas típicas
            $geometry = [
                'type' => Arr::get($geometry, 'type'),
                'coordinates' => Arr::get($geometry, 'coordinates'),
            ];
        }

        $this->merge([
            'name' => $name,
            'slug' => $slug,
            'description' => $description === '' ? null : $description,
            'capacity' => $capacity === '' ? null : $capacity,
            'metadata' => $metadata === '' ? null : $metadata,
            'geometry' => $geometry,
        ]);
    }

    public function rules(): array {
        return [
            'parking_zone_type_id' => ['bail', 'required', 'integer', 'exists:parking_zone_types,id'],
            'name'                 => ['bail', 'required', 'string', 'max:160'],
            'slug'                 => ['bail', 'required', 'string', 'max:180', 'alpha_dash'],
            'description'          => ['nullable', 'string'],
            'capacity'             => ['nullable', 'integer', 'min:1'],
            'status'               => ['bail', 'required', 'in:active,inactive,draft'],

            // GeoJSON básico
            'geometry'                 => ['bail', 'required', 'array'],
            'geometry.type'            => ['bail', 'required', 'in:Polygon,MultiPolygon'],
            'geometry.coordinates'     => ['bail', 'required', 'array', 'min:1'], // al menos un anillo / polígono
            // Nota: si quisieras validar más profundamente el array de coords,
            // habría que hacer una regla custom (por rendimiento/legibilidad).

            'metadata'              => ['nullable', 'array'],
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

            'description.string' => 'La descripción debe ser un texto.',

            'capacity.integer' => 'La capacidad debe ser un número entero.',
            'capacity.min'     => 'La capacidad mínima es :min.',

            'status.required' => 'El estado es obligatorio.',
            'status.in'       => 'El estado debe ser uno de: active, inactive o draft.',

            'geometry.required'             => 'La geometría es obligatoria.',
            'geometry.array'                => 'La geometría debe ser un objeto/array válido.',
            'geometry.type.required'        => 'El tipo de geometría es obligatorio.',
            'geometry.type.in'              => 'El tipo de geometría debe ser Polygon o MultiPolygon.',
            'geometry.coordinates.required' => 'Las coordenadas de la geometría son obligatorias.',
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
