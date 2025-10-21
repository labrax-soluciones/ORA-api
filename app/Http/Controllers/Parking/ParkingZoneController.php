<?php

namespace App\Http\Controllers\Parking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Parking\StoreParkingZoneBasicRequest;
use App\Http\Requests\Parking\StoreParkingZoneRequest;
use App\Http\Requests\Parking\UpdateParkingZoneBasicRequest;
use App\Http\Requests\Parking\UpdateParkingZoneRequest;
use App\Http\Requests\Parking\UpsertParkingZoneGeometryRequest;
use App\Models\Municipality;
use App\Models\ParkingZone;
use App\Models\ParkingZoneType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ParkingZoneController extends Controller {
    public function index(Municipality $municipality, Request $request) {
        $q = ParkingZone::query()
            ->with(['type:id,municipality_id,name,slug,color_hex'])
            ->where('municipality_id', $municipality->id)
            ->when($request->query('type_id'), fn($qq, $id) => $qq->where('parking_zone_type_id', $id))
            ->when($request->query('status'), fn($qq, $s) => $qq->where('status', $s));

        return response()->json($q->orderBy('name')->paginate($request->integer('per_page', 25)));
    }

    public function show(Municipality $municipality, ParkingZone $zone) {
        $this->assertBelongsToMunicipality($municipality, $zone->municipality_id);
        return response()->json($zone->load('type'));
    }

    public function store(Municipality $municipality, StoreParkingZoneRequest $request) {
        $data = $request->validated();

        // seguridad: el tipo debe pertenecer al mismo municipio
        $type = ParkingZoneType::findOrFail($data['parking_zone_type_id']);
        abort_if($type->municipality_id !== $municipality->id, 422, 'Type does not belong to this municipality.');

        $data['municipality_id'] = $municipality->id;

        $zone = ParkingZone::updateOrCreate(
            ['municipality_id' => $municipality->id, 'slug' => $data['slug']],
            $data
        );

        return response()->json($zone->load('type'), Response::HTTP_CREATED);
    }

    public function update(Municipality $municipality, ParkingZone $zone, UpdateParkingZoneRequest $request) {
        $this->assertBelongsToMunicipality($municipality, $zone->municipality_id);
        $payload = $request->validated();

        if (isset($payload['parking_zone_type_id'])) {
            $type = ParkingZoneType::findOrFail($payload['parking_zone_type_id']);
            abort_if($type->municipality_id !== $municipality->id, 422, 'Type does not belong to this municipality.');
        }

        if (isset($payload['slug'])) {
            $exists = ParkingZone::where('municipality_id', $municipality->id)
                ->where('slug', $payload['slug'])
                ->where('id', '<>', $zone->id)
                ->exists();
            abort_if($exists, 422, 'Slug already exists in this municipality.');
        }

        $zone->fill($payload)->save();
        return response()->json($zone->load('type'));
    }

    public function destroy(Municipality $municipality, ParkingZone $zone) {
        $this->assertBelongsToMunicipality($municipality, $zone->municipality_id);
        $zone->delete();
        return response()->json(['deleted' => true]);
    }

    /**
     * Crear zona SOLO con datos básicos (sin geometría).
     */
    public function storeBasic(Municipality $municipality, StoreParkingZoneBasicRequest $request) {
        $data = $request->validated();

        // seguridad: el tipo debe pertenecer al municipio
        $type = ParkingZoneType::findOrFail($data['parking_zone_type_id']);
        abort_if($type->municipality_id !== $municipality->id, 422, 'El tipo no pertenece a este municipio.');

        $data['municipality_id'] = $municipality->id;
        $data['geometry'] = null; // explícito

        // upsert por slug dentro del municipio
        $zone = ParkingZone::updateOrCreate(
            ['municipality_id' => $municipality->id, 'slug' => $data['slug']],
            $data
        );

        return response()->json($zone->load('type'), Response::HTTP_CREATED);
    }

    /**
     * Editar SOLO datos básicos (sin tocar geometría).
     */
    public function updateBasic(Municipality $municipality, ParkingZone $zone, UpdateParkingZoneBasicRequest $request) {
        $this->assertBelongsToMunicipality($municipality, $zone->municipality_id);
        $payload = $request->validated();

        if (isset($payload['parking_zone_type_id'])) {
            $type = ParkingZoneType::findOrFail($payload['parking_zone_type_id']);
            abort_if($type->municipality_id !== $municipality->id, 422, 'El tipo no pertenece a este municipio.');
        }

        if (isset($payload['slug'])) {
            $exists = ParkingZone::where('municipality_id', $municipality->id)
                ->where('slug', $payload['slug'])
                ->where('id', '<>', $zone->id)
                ->exists();
            abort_if($exists, 422, 'Ya existe una zona con ese slug en este municipio.');
        }

        // Importante: NO tocar geometry aquí
        $zone->fill($payload)->save();

        return response()->json($zone->load('type'));
    }

    /**
     * Crear/actualizar SOLO la geometría.
     */
    public function upsertGeometry(Municipality $municipality, ParkingZone $zone, UpsertParkingZoneGeometryRequest $request) {
        $this->assertBelongsToMunicipality($municipality, $zone->municipality_id);

        $geo = $request->validated()['geometry']; // siempre requerido aquí
        $zone->geometry = $geo;
        $zone->save();

        return response()->json([
            'ok' => true,
            'zone' => $zone->fresh()->load('type'),
        ]);
    }

    /**
     * Eliminar geometría (dejarla a null).
     */
    public function deleteGeometry(Municipality $municipality, ParkingZone $zone) {
        $this->assertBelongsToMunicipality($municipality, $zone->municipality_id);

        $zone->geometry = null;
        $zone->save();

        return response()->json(['ok' => true]);
    }

    private function assertBelongsToMunicipality(Municipality $m, int $ownerId): void {
        abort_if($m->id !== $ownerId, 404, 'Resource not found in municipality.');
    }
}
