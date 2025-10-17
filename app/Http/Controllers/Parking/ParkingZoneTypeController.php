<?php

namespace App\Http\Controllers\Parking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Parking\StoreParkingZoneTypeRequest;
use App\Http\Requests\Parking\UpdateParkingZoneTypeRequest;
use App\Models\Municipality;
use App\Models\ParkingZoneType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ParkingZoneTypeController extends Controller {
    public function index(Municipality $municipality, Request $request) {
        $q = ParkingZoneType::query()
            ->where('municipality_id', $municipality->id)
            ->when($request->query('status'), fn($qq, $s) => $qq->where('status', $s));

        return response()->json($q->orderBy('name')->paginate($request->integer('per_page', 25)));
    }

    public function store(Municipality $municipality, StoreParkingZoneTypeRequest $request) {
        $data = $request->validated();
        $data['municipality_id'] = $municipality->id;

        // evita duplicados por slug dentro del municipio
        $type = ParkingZoneType::updateOrCreate(
            ['municipality_id' => $municipality->id, 'slug' => $data['slug']],
            $data
        );

        return response()->json($type, Response::HTTP_CREATED);
    }

    public function show(Municipality $municipality, ParkingZoneType $type) {
        $this->assertBelongsToMunicipality($municipality, $type->municipality_id);
        return response()->json($type->loadCount('zones')->load('schedules'));
    }

    public function update(Municipality $municipality, ParkingZoneType $type, UpdateParkingZoneTypeRequest $request) {
        $this->assertBelongsToMunicipality($municipality, $type->municipality_id);
        $payload = $request->validated();

        // si cambia slug, mantener unique por municipio
        if (isset($payload['slug'])) {
            $exists = ParkingZoneType::where('municipality_id', $municipality->id)
                ->where('slug', $payload['slug'])
                ->where('id', '<>', $type->id)
                ->exists();
            abort_if($exists, 422, 'Slug already exists in this municipality.');
        }

        $type->fill($payload)->save();
        return response()->json($type);
    }

    public function destroy(Municipality $municipality, ParkingZoneType $type) {
        $this->assertBelongsToMunicipality($municipality, $type->municipality_id);
        $type->delete();
        return response()->json(['deleted' => true]);
    }

    private function assertBelongsToMunicipality(Municipality $m, int $ownerId): void {
        abort_if($m->id !== $ownerId, 404, 'Resource not found in municipality.');
    }
}
