<?php

namespace App\Http\Controllers\Parking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Parking\StoreParkingZoneTypeScheduleRequest;
use App\Http\Requests\Parking\UpdateParkingZoneTypeScheduleRequest;
use App\Models\Municipality;
use App\Models\ParkingZoneType;
use App\Models\ParkingZoneTypeSchedule;
use Illuminate\Http\Response;

class ParkingZoneTypeScheduleController extends Controller {
    public function index(Municipality $municipality, ParkingZoneType $type) {
        $this->assertBelongsToMunicipality($municipality, $type->municipality_id);
        return response()->json(
            $type->schedules()->orderBy('day_of_week')->orderBy('start_time')->get()
        );
    }

    public function store(Municipality $municipality, ParkingZoneType $type, StoreParkingZoneTypeScheduleRequest $request) {
        $this->assertBelongsToMunicipality($municipality, $type->municipality_id);
        $data = $request->validated();
        $data['parking_zone_type_id'] = $type->id;

        // respetamos el unique (type, day, start, end)
        $sched = ParkingZoneTypeSchedule::updateOrCreate(
            [
                'parking_zone_type_id' => $type->id,
                'day_of_week' => $data['day_of_week'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
            ],
            [
                'timezone' => $data['timezone'] ?? null,
                'is_holiday' => $data['is_holiday'] ?? false,
                'settings' => $data['settings'] ?? null,
            ]
        );

        return response()->json($sched, Response::HTTP_CREATED);
    }

    public function update(
        Municipality $municipality,
        ParkingZoneType $type,
        ParkingZoneTypeSchedule $schedule,
        UpdateParkingZoneTypeScheduleRequest $request
    ) {
        $this->assertBelongsToMunicipality($municipality, $type->municipality_id);
        abort_if($schedule->parking_zone_type_id !== $type->id, 404, 'Schedule not found for this type.');

        $payload = $request->validated();

        // Si se cambian claves del unique, valida duplicados
        $newDay = $payload['day_of_week'] ?? $schedule->day_of_week;
        $newStart = $payload['start_time'] ?? $schedule->start_time;
        $newEnd = $payload['end_time'] ?? $schedule->end_time;

        $exists = ParkingZoneTypeSchedule::where('parking_zone_type_id', $type->id)
            ->where('day_of_week', $newDay)
            ->where('start_time', $newStart)
            ->where('end_time', $newEnd)
            ->where('id', '<>', $schedule->id)
            ->exists();
        abort_if($exists, 422, 'Schedule interval already exists.');

        $schedule->fill($payload)->save();
        return response()->json($schedule);
    }

    public function destroy(Municipality $municipality, ParkingZoneType $type, ParkingZoneTypeSchedule $schedule) {
        $this->assertBelongsToMunicipality($municipality, $type->municipality_id);
        abort_if($schedule->parking_zone_type_id !== $type->id, 404, 'Schedule not found for this type.');

        $schedule->delete();
        return response()->json(['deleted' => true]);
    }

    private function assertBelongsToMunicipality(Municipality $m, int $ownerId): void {
        abort_if($m->id !== $ownerId, 404, 'Resource not found in municipality.');
    }
}
