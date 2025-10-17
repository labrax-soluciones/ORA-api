<?php

namespace App\Http\Controllers;

use App\Enums\MunicipalityStatus;
use App\Http\Requests\MunicipalityStoreRequest;
use App\Http\Requests\MunicipalityUpdateRequest;
use App\Models\Municipality;
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
}
