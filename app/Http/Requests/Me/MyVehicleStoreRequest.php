<?php

namespace App\Http\Requests\Me;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MyVehicleStoreRequest extends FormRequest {
    public function authorize(): bool {
        return true;
    }

    protected function prepareForValidation(): void {
        foreach (['brand', 'model', 'color', 'license_plate'] as $k) {
            if ($this->filled($k)) {
                $this->merge([$k => trim((string)$this->input($k))]);
            }
        }
        if ($this->input('status') === null) {
            $this->merge(['status' => 'active']); // por defecto
        }
    }

    public function rules(): array {
        $userId = (int) $this->user()->id;

        return [
            'brand'         => ['nullable', 'string', 'max:120'],
            'model'         => ['nullable', 'string', 'max:120'],
            'color'         => ['nullable', 'string', 'max:60'],
            'license_plate' => [
                'required',
                'string',
                'max:40',
                Rule::unique('vehicles', 'license_plate')->where('user_id', $userId),
            ],
            // El propio usuario SOLO puede fijar active|inactive
            'status'        => ['nullable', 'in:active,inactive'],
            'year'          => ['nullable', 'integer', 'digits:4', 'min:1900', 'max:' . (int)date('Y') + 1],
            'meta'          => ['nullable', 'array'],
        ];
    }

    public function messages(): array {
        return [
            'license_plate.required' => 'La matrícula es obligatoria.',
            'license_plate.unique'   => 'Ya tienes un vehículo con esa matrícula.',
            'status.in'              => 'El estado debe ser active o inactive.',
            'year.integer'           => 'El año debe ser un número.',
            'year.digits'            => 'El año debe tener 4 dígitos.',
            'year.min'               => 'El año no puede ser anterior a 1900.',
            'year.max'               => 'El año no puede ser mayor que el actual+1.',
        ];
    }

    public function attributes(): array {
        return [
            'brand' => 'marca',
            'model' => 'modelo',
            'color' => 'color',
            'license_plate' => 'matrícula',
            'status' => 'estado',
            'year' => 'año',
        ];
    }
}
