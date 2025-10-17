<?php

namespace App\Http\Requests\Parking;

use Illuminate\Foundation\Http\FormRequest;

class StoreParkingZoneRequest extends FormRequest {
    public function authorize(): bool {
        return true;
    }

    public function rules(): array {
        return [
            'parking_zone_type_id' => ['required', 'integer', 'exists:parking_zone_types,id'],
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['required', 'string', 'max:180', 'alpha_dash'],
            'description' => ['nullable', 'string'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', 'in:active,inactive,draft'],
            // GeoJSON básico
            'geometry' => ['required', 'array'],
            'geometry.type' => ['required', 'in:Polygon,MultiPolygon'],
            'geometry.coordinates' => ['required', 'array'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
