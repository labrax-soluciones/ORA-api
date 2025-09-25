<?php

namespace App\Http\Controllers;

use App\Enums\RoleName;
use App\Http\Requests\MunicipalAdminStoreRequest;
use App\Http\Requests\MunicipalAdminUpdateRequest;
use App\Models\MunicipalAdminProfile;
use App\Models\Municipality;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MunicipalAdminController extends Controller
{
    // GET /municipalities/{municipality}/admins
    public function index(Request $request, Municipality $municipality)
    {
        $items = MunicipalAdminProfile::query()
            ->with(['user:id,first_name,last_name,email,phone'])
            ->where('municipality_id', $municipality->id)
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 20));

        return response()->json($items);
    }

    // POST /municipalities/{municipality}/admins
    public function store(MunicipalAdminStoreRequest $request, Municipality $municipality)
    {
        $data = $request->validated();

        $user = DB::transaction(function () use ($data, $municipality) {
            $user = User::create([
                'email'       => $data['email'],
                'first_name'  => $data['first_name'],
                'last_name'   => $data['last_name'],
                'name'        => trim($data['first_name'].' '.$data['last_name']),
                'phone'       => $data['phone'] ?? null,
                'id_document' => $data['id_document'] ?? null,
                // password temporal; en real, invitar a setear contraseña
                'password'    => Hash::make(str()->password(12)),
            ]);

            $user->assignRole(RoleName::MUNICIPAL_ADMIN->value);

            MunicipalAdminProfile::create([
                'user_id'         => $user->id,
                'municipality_id' => $municipality->id,
                'phone'           => $data['phone'] ?? null,
                'id_document'     => $data['id_document'] ?? null,
            ]);

            return $user;
        });

        $profile = MunicipalAdminProfile::where('user_id', $user->id)
            ->with('municipality:id,name,slug')
            ->first();

        return response()->json([
            'user'    => $user->only(['id', 'first_name', 'last_name', 'email', 'phone']),
            'profile' => $profile,
        ], 201);
    }

    // PUT /municipalities/{municipality}/admins/{admin}
    public function update(MunicipalAdminUpdateRequest $request, Municipality $municipality, MunicipalAdminProfile $admin)
    {
        if ((int)$admin->municipality_id !== (int)$municipality->id) {
            abort(404);
        }

        $data = $request->validated();

        DB::transaction(function () use ($admin, $data) {
            // actualizar user
            $admin->user->update([
                'email'       => $data['email'],
                'first_name'  => $data['first_name'],
                'last_name'   => $data['last_name'],
                'phone'       => $data['phone'] ?? $admin->user->phone,
                'id_document' => $data['id_document'] ?? $admin->user->id_document,
                // name se sincroniza en booted() del User
            ]);

            // actualizar perfil
            $admin->update([
                'phone'       => $data['phone'] ?? $admin->phone,
                'id_document' => $data['id_document'] ?? $admin->id_document,
            ]);
        });

        $admin->load(['user:id,first_name,last_name,email,phone', 'municipality:id,name,slug']);

        return response()->json([
            'user'    => $admin->user,
            'profile' => $admin,
        ]);
    }

    // DELETE /municipalities/{municipality}/admins/{admin}
    public function destroy(Request $request, Municipality $municipality, MunicipalAdminProfile $admin)
    {
        if ((int)$admin->municipality_id !== (int)$municipality->id) {
            abort(404);
        }

        DB::transaction(function () use ($admin) {
            // quitar rol municipal_admin (conservamos el usuario)
            if ($admin->user->hasRole(RoleName::MUNICIPAL_ADMIN->value)) {
                $admin->user->removeRole(RoleName::MUNICIPAL_ADMIN->value);
            }
            $admin->delete();
        });

        return response()->json(['message' => 'Administrador municipal eliminado'], 200);
    }
}
