<?php

namespace App\Http\Requests\Parking;

use Illuminate\Foundation\Http\FormRequest;

class UpdateParkingZoneTypeRequest extends FormRequest {
    public function authorize(): bool {
        return true;
    }

    public function rules(): array {
        return [
            'name' => ['sometimes', 'string', 'max:120'],
            'slug' => ['sometimes', 'string', 'max:140', 'alpha_dash'],
            'color_hex' => ['sometimes', 'nullable', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{8})$/'],
            'max_stay_minutes' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'outside_schedule_policy' => ['sometimes', 'in:unlimited,forbidden,same_as_inside'],
            'status' => ['sometimes', 'in:active,inactive'],
            'settings' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
