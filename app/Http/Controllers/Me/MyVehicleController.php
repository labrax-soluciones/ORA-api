<?php

namespace App\Http\Controllers\Me;

use App\Http\Controllers\Controller;
use App\Http\Requests\Me\MyVehicleStoreRequest;
use App\Http\Requests\Me\MyVehicleUpdateRequest;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class MyVehicleController extends Controller {
    // GET /me/vehicles
    public function index(Request $request) {
        $user = $request->user();

        $items = Vehicle::query()
            ->where('user_id', $user->id)
            ->when($request->filled('status'), fn($q) =>
            $q->where('status', $request->get('status')))
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 20));

        return response()->json($items);
    }

    // GET /me/vehicles/{vehicle}
    public function show(Request $request, Vehicle $vehicle) {
        if ((int)$vehicle->user_id !== (int)$request->user()->id) {
            abort(404);
        }

        return response()->json($vehicle);
    }

    // POST /me/vehicles
    public function store(MyVehicleStoreRequest $request) {
        $user = $request->user();

        $vehicle = Vehicle::create(array_merge(
            $request->validated(),
            ['user_id' => $user->id]
        ));

        return response()->json($vehicle, 201);
    }

    // PUT/PATCH /me/vehicles/{vehicle}
    public function update(MyVehicleUpdateRequest $request, Vehicle $vehicle) {
        if ((int)$vehicle->user_id !== (int)$request->user()->id) {
            abort(404);
        }

        $vehicle->update($request->validated());

        return response()->json($vehicle);
    }
}
