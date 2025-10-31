<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) {

        $items = User::query()
            ->orderBy('name')
            ->paginate($request->integer('per_page', 20));

        return response()->json($items);
    }

    // aun por provar index con filtros

    // public function index(Request $request) {
    //     $perPage = $request->integer('per_page', 20);
    //     $search  = trim((string)$request->get('q', ''));

    //     $items = User::query()
    //         ->with(['profile'])
    //         // Usuarios que tengan rol USER
    //         ->whereHas('roles', fn($q) => $q->where('name', RoleName::USER->value))
    //         // Búsqueda opcional por nombre/email
    //         ->when($search !== '', function ($q) use ($search) {
    //             $q->where(function ($w) use ($search) {
    //                 $w->where('first_name', 'like', "%{$search}%")
    //                     ->orWhere('last_name', 'like', "%{$search}%")
    //                     ->orWhere('email', 'like', "%{$search}%");
    //             });
    //         })
    //         ->orderBy('last_name')
    //         ->orderBy('first_name')
    //         ->paginate($perPage);

    //     return response()->json($items);
    // }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request) {
        $user = User::create($request->validated());
        return response()->json($user, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user) {
        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, User $user) {
        $user->update($request->validated());
        return response()->json($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user) {
        $user->delete();
        return response()->json(null, 204);
    }

    // CUALQUIER autenticado puede ver un “perfil público” básico de cualquier usuario
    public function publicShow(User $user) {
        $user->loadMissing('profile');

        return response()->json([
            'id'         => $user->id,
            'first_name' => $user->first_name,
            'last_name'  => $user->last_name,
            // Muestra los campos de perfil no sensibles:
            'profile'    => $user->profile?->only([
                'city',
                'province',
                'country'
            ]),
        ]);
    }
}
