<?php

namespace App\Http\Requests\Me;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MyVehicleUpdateRequest extends FormRequest {
    public function authorize(): bool {
        return true;
    }

    protected function prepareForValidation(): void {
        foreach (['brand', 'model', 'color', 'license_plate', 'status'] as $k) {
            if ($this->filled($k) && $this->input($k) === '') {
                $this->merge([$k => null]);
            }
        }
    }

    public function rules(): array {
        $userId = (int) $this->user()->id;
        $vehicleId = (int) optional($this->route('vehicle'))->id;

        return [
            'brand'         => ['nullable', 'string', 'max:120'],
            'model'         => ['nullable', 'string', 'max:120'],
            'color'         => ['nullable', 'string', 'max:60'],
            'license_plate' => [
                'nullable',
                'string',
                'max:40',
                Rule::unique('vehicles', 'license_plate')
                    ->where('user_id', $userId)
                    ->ignore($vehicleId),
            ],
            // Solo active|inactive para el propio usuario
            'status'        => ['nullable', 'in:active,inactive'],
            'year'          => ['nullable', 'integer', 'digits:4', 'min:1900', 'max:' . (int)date('Y') + 1],
            'meta'          => ['nullable', 'array'],
        ];
    }

    public function messages(): array {
        return [
            'license_plate.unique' => 'Ya tienes otro vehículo con esa matrícula.',
            'status.in'            => 'El estado debe ser active o inactive.',
        ];
    }

    public function attributes(): array {
        return [
            'license_plate' => 'matrícula',
            'status'        => 'estado',
        ];
    }
}
