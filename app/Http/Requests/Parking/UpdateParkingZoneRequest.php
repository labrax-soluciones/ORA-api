<?php

namespace App\Http\Requests\Parking;

use Illuminate\Foundation\Http\FormRequest;

class UpdateParkingZoneRequest extends FormRequest {
    public function authorize(): bool {
        return true;
    }

    public function rules(): array {
        return [
            'parking_zone_type_id' => ['sometimes', 'integer', 'exists:parking_zone_types,id'],
            'name' => ['sometimes', 'string', 'max:160'],
            'slug' => ['sometimes', 'string', 'max:180', 'alpha_dash'],
            'description' => ['sometimes', 'nullable', 'string'],
            'capacity' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'status' => ['sometimes', 'in:active,inactive,draft'],
            'geometry' => ['sometimes', 'array'],
            'geometry.type' => ['sometimes', 'in:Polygon,MultiPolygon'],
            'geometry.coordinates' => ['sometimes', 'array'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
