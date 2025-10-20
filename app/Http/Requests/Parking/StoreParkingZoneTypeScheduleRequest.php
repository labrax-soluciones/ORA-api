<?php

namespace App\Http\Requests\Parking;

use Illuminate\Foundation\Http\FormRequest;

class StoreParkingZoneTypeScheduleRequest extends FormRequest {
    public function authorize(): bool {
        return true;
    }

    /**
     * Normaliza:
     * - day_of_week a int
     * - start_time / end_time: 'HH:mm:ss' -> 'HH:mm'
     * - timezone: '' -> null
     * - is_holiday: por defecto false si no viene
     */
    protected function prepareForValidation(): void {
        $hhmm = function ($v) {
            if (!is_string($v)) return $v;
            // acepta HH:mm o HH:mm:ss y devuelve HH:mm
            if (preg_match('/^(\d{2}:\d{2})(?::\d{2})?$/', $v, $m)) {
                return $m[1];
            }
            return $v;
        };

        $day = $this->input('day_of_week');

        $this->merge([
            'day_of_week' => is_numeric($day) ? (int) $day : $day,
            'start_time'  => $hhmm($this->input('start_time')),
            'end_time'    => $hhmm($this->input('end_time')),
            'timezone'    => $this->input('timezone') === '' ? null : $this->input('timezone'),
            'is_holiday'  => $this->has('is_holiday') ? $this->input('is_holiday') : false,
        ]);
    }

    public function rules(): array {
        return [
            'day_of_week' => ['bail', 'required', 'integer', 'between:1,7'],
            'start_time'  => ['bail', 'required', 'date_format:H:i'],
            'end_time'    => ['bail', 'required', 'date_format:H:i', 'after:start_time'],
            'timezone'    => ['nullable', 'string', 'max:64'],
            'is_holiday'  => ['sometimes', 'boolean'],
            'settings'    => ['nullable', 'array'],
        ];
    }

    public function messages(): array {
        return [
            'day_of_week.required' => 'El día de la semana es obligatorio.',
            'day_of_week.integer'  => 'El día de la semana debe ser un número entero.',
            'day_of_week.between'  => 'El día debe estar entre 1 (lunes) y 7 (domingo).',

            'start_time.required'    => 'La hora de inicio es obligatoria.',
            'start_time.date_format' => 'La hora de inicio debe tener formato HH:MM.',

            'end_time.required'    => 'La hora de fin es obligatoria.',
            'end_time.date_format' => 'La hora de fin debe tener formato HH:MM.',
            'end_time.after'       => 'La hora de fin debe ser posterior a la de inicio.',

            'timezone.string' => 'La zona horaria debe ser un texto.',
            'timezone.max'    => 'La zona horaria no puede superar los :max caracteres.',

            'is_holiday.boolean' => 'El campo "es festivo" debe ser verdadero o falso.',

            'settings.array' => 'Los ajustes deben ser un objeto/array.',
        ];
    }

    public function attributes(): array {
        return [
            'day_of_week' => 'día de la semana',
            'start_time'  => 'hora de inicio',
            'end_time'    => 'hora de fin',
            'timezone'    => 'zona horaria',
            'is_holiday'  => 'es festivo',
            'settings'    => 'ajustes',
        ];
    }
}
