<?php

namespace App\Http\Requests\Parking;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class UpsertParkingZoneGeometryRequest extends FormRequest {
    public function authorize(): bool {
        return true;
    }

    protected function prepareForValidation(): void {
        $geometry = $this->input('geometry');
        if (is_array($geometry)) {
            $geometry = [
                'type'        => Arr::get($geometry, 'type'),
                'coordinates' => Arr::get($geometry, 'coordinates'),
            ];
        }
        $this->merge(['geometry' => $geometry]);
    }

    public function rules(): array {
        return [
            'geometry'                 => ['bail', 'required', 'array'],
            'geometry.type'            => ['bail', 'required', 'in:Polygon,MultiPolygon'],
            'geometry.coordinates'     => ['bail', 'required', 'array', 'min:1'],
        ];
    }

    public function messages(): array {
        return [
            'geometry.required'             => 'La geometría es obligatoria.',
            'geometry.array'                => 'La geometría debe ser un objeto/array válido.',
            'geometry.type.required'        => 'El tipo de geometría es obligatorio.',
            'geometry.type.in'              => 'El tipo de geometría debe ser Polygon o MultiPolygon.',
            'geometry.coordinates.required' => 'Las coordenadas de la geometría son obligatorias.',
            'geometry.coordinates.array'    => 'Las coordenadas de la geometría deben ser un array.',
            'geometry.coordinates.min'      => 'La geometría debe contener al menos un polígono válido.',
        ];
    }

    public function attributes(): array {
        return [
            'geometry'                 => 'geometría',
            'geometry.type'            => 'tipo de geometría',
            'geometry.coordinates'     => 'coordenadas de la geometría',
        ];
    }
}
