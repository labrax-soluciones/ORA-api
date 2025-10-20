<?php

namespace App\Http\Controllers;

use App\Enums\MunicipalityStatus;
use App\Http\Requests\MunicipalityStoreRequest;
use App\Http\Requests\MunicipalityUpdateRequest;
use App\Models\MunicipalAdminProfile;
use App\Models\Municipality;
use App\Models\ParkingZone;
use App\Models\ParkingZoneType;
use App\Models\PoliceProfile;
use App\Models\TechnicianProfile;
use Illuminate\Http\Request;

class MunicipalityController extends Controller {
    // Listado paginado
    public function index(Request $request) {
        $items = Municipality::query()
            ->orderBy('name')
            ->paginate($request->integer('per_page', 20));

        return response()->json($items);
    }

    // Alta (solo admin)
    public function store(MunicipalityStoreRequest $request) {
        $mun = Municipality::create($request->validated());
        return response()->json($mun, 201);
    }

    // Detalle
    public function show(Municipality $municipality) {
        return response()->json($municipality);
    }

    // Endpoint  publico para ver datos basicos
    public function publicBySlug(string $slug) {
        $m = Municipality::query()
            ->where('slug', $slug)
            ->where('status', MunicipalityStatus::Active)
            ->firstOrFail();

        // Devuelve SOLO metadatos públicos necesarios para UI
        return response()->json([
            'id'        => $m->id,
            'slug'      => $m->slug,
            'name'      => $m->name,
            'updated_at' => $m->updated_at?->toIso8601String(),
        ]);
    }


    // Update
    public function update(MunicipalityUpdateRequest $request, Municipality $municipality) {
        $municipality->update($request->validated());
        return response()->json($municipality);
    }

    // Soft delete
    public function destroy(Municipality $municipality) {
        $municipality->delete();
        return response()->noContent();
    }

    // (Opcional) Restaurar un soft delete
    public function restore($id) {
        $mun = Municipality::withTrashed()->findOrFail($id);
        $mun->restore();
        return response()->json($mun);
    }

    public function stats(Municipality $municipality) {
        $policeCount      = PoliceProfile::where('municipality_id', $municipality->id)->count();
        $technicianCount  = TechnicianProfile::where('municipality_id', $municipality->id)->count();
        $adminCount       = MunicipalAdminProfile::where('municipality_id', $municipality->id)->count();

        $zoneCount        = ParkingZone::where('municipality_id', $municipality->id)->count();
        $zoneTypesCount   = ParkingZoneType::where('municipality_id', $municipality->id)->count();

        return response()->json([
            'police_count'           => $policeCount,
            'technician_count'       => $technicianCount,
            'municipal_admin_count'  => $adminCount,
            'zone_count'             => $zoneCount,
            'zone_types_count'       => $zoneTypesCount,
        ]);
    }
}
