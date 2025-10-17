<?php

namespace App\Http\Requests\Parking;

use Illuminate\Foundation\Http\FormRequest;

class StoreParkingZoneTypeRequest extends FormRequest {
    public function authorize(): bool {
        return true;
    }

    public function rules(): array {
        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:140', 'alpha_dash'],
            'color_hex' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{8})$/'],
            'max_stay_minutes' => ['nullable', 'integer', 'min:1'],
            'outside_schedule_policy' => ['required', 'in:unlimited,forbidden,same_as_inside'],
            'status' => ['required', 'in:active,inactive'],
            'settings' => ['nullable', 'array'],
        ];
    }
}
