<?php

namespace App\Http\Requests\Parking;

use Illuminate\Foundation\Http\FormRequest;

class UpdateParkingZoneTypeScheduleRequest extends FormRequest {
    public function authorize(): bool {
        return true;
    }

    public function rules(): array {
        return [
            'day_of_week' => ['sometimes', 'integer', 'between:1,7'],
            'start_time' => ['sometimes', 'date_format:H:i'],
            'end_time' => ['sometimes', 'date_format:H:i', 'after:start_time'],
            'timezone' => ['sometimes', 'nullable', 'string', 'max:64'],
            'is_holiday' => ['sometimes', 'boolean'],
            'settings' => ['sometimes', 'nullable', 'array'],
        ];
    }

    public function messages(): array {
        return [
            'start_time.date_format' => 'La hora de inicio debe tener formato HH:MM.',
            'end_time.date_format'   => 'La hora de fin debe tener formato HH:MM.',
            'end_time.after'         => 'La hora de fin debe ser posterior a la de inicio.',
            'day_of_week.between'    => 'El día debe estar entre 1 (lunes) y 7 (domingo).',
        ];
    }

    public function attributes(): array {
        return [
            'day_of_week' => 'día de la semana',
            'start_time'  => 'hora de inicio',
            'end_time'    => 'hora de fin',
        ];
    }

    protected function prepareForValidation(): void {
        /** @var \App\Models\ParkingZoneTypeSchedule $schedule */
        $schedule = $this->route('schedule');

        // Si llega solo end_time, inyecta el start_time actual para que 'after:start_time' funcione
        if ($this->has('end_time') && !$this->has('start_time') && $schedule) {
            $this->merge(['start_time' => $schedule->start_time]);
        }

        // Si llega solo start_time, inyecta el end_time actual para poder validar coherencia si lo necesitas
        if ($this->has('start_time') && !$this->has('end_time') && $schedule) {
            $this->merge(['end_time' => $schedule->end_time]);
        }
    }
}
