<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleBrowseController extends Controller {
    // GET /users/{user}/vehicles
    public function index(Request $request, User $user) {
        $items = Vehicle::query()
            ->where('user_id', $user->id)
            ->when($request->filled('status'), fn($q) =>
            $q->where('status', $request->get('status')))
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 20));

        return response()->json([
            'user'  => $user->only(['id', 'first_name', 'last_name', 'email']),
            'items' => $items,
        ]);
    }

    // GET /users/{user}/vehicles/{vehicle}
    public function show(Request $request, User $user, Vehicle $vehicle) {
        if ((int)$vehicle->user_id !== (int)$user->id) {
            abort(404);
        }

        return response()->json([
            'user'    => $user->only(['id', 'first_name', 'last_name', 'email']),
            'vehicle' => $vehicle,
        ]);
    }
}
