<?php

namespace App\Http\Controllers;

use App\Http\Requests\MunicipalityStoreRequest;
use App\Http\Requests\MunicipalityUpdateRequest;
use App\Models\Municipality;
use Illuminate\Http\Request;

class MunicipalityController extends Controller {
    // Listado paginado (visible para perfiles con permiso municipal)
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


    public function update(MunicipalityUpdateRequest $request, Municipality $municipality) {
        $municipality->update($request->validated());
        return response()->json($municipality);
    }
}
