<?php

namespace App\Http\Controllers;

use App\Enums\RoleName;
use App\Http\Requests\TechnicianStoreRequest;
use App\Http\Requests\TechnicianUpdateRequest;
use App\Models\Municipality;
use App\Models\TechnicianProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TechnicianController extends Controller {

    // GET /municipalities/{municipality}/technicians
    public function index(Request $request, Municipality $municipality) {

        $items = TechnicianProfile::query()
            ->with(['user:id,first_name,last_name,email,phone'])
            ->where('municipality_id', $municipality->id)
            ->orderBy('id', 'desc')
            ->paginate($request->integer('per_page', 20));

        return response()->json($items);
    }

    // POST /municipalities/{municipality}/technicians
    public function store(TechnicianStoreRequest $request, Municipality $municipality) {
        $data = $request->validated();

        $user = DB::transaction(function () use ($data, $municipality) {
            $user = User::create([
                'email'       => $data['email'],
                'first_name'  => $data['first_name'],
                'last_name'   => $data['last_name'],
                'name'        => trim($data['first_name'] . ' ' . $data['last_name']),
                'phone'       => $data['phone'] ?? null,
                'id_document' => $data['id_document'] ?? null,
                // Clave temporal — en real, envía invitación para establecer password
                'password'    => Hash::make(str()->password(12)),
            ]);

            $user->assignRole(RoleName::TECHNICIAN->value);

            TechnicianProfile::create([
                'user_id'         => $user->id,
                'municipality_id' => $municipality->id,
                'department'      => $data['department'] ?? null,
                'position'        => $data['position'] ?? null,
                'phone'           => $data['phone'] ?? null,
                'id_document'     => $data['id_document'] ?? null,
            ]);

            return $user;
        });

        $profile = $user->technicianProfile()->with('municipality')->first();

        return response()->json([
            'user'    => $user->only(['id', 'first_name', 'last_name', 'email', 'phone']),
            'profile' => $profile,
        ], 201);
    }

    // PUT /municipalities/{municipality}/technicians/{technician}
    public function update(TechnicianUpdateRequest $request, Municipality $municipality, TechnicianProfile $technician) {
        // asegurar que el técnico pertenece al municipio de la ruta
        if ((int)$technician->municipality_id !== (int)$municipality->id) {
            abort(404); // no revelar otros municipios
        }

        $data = $request->validated();

        DB::transaction(function () use ($technician, $data) {
            // actualizar usuario
            $technician->user->update([
                'email'       => $data['email'],
                'first_name'  => $data['first_name'],
                'last_name'   => $data['last_name'],
                'phone'       => $data['phone'] ?? $technician->user->phone,
                'id_document' => $data['id_document'] ?? $technician->user->id_document,
                // name se autogenera en booted()
            ]);

            // actualizar perfil técnico
            $technician->update([
                'department'  => $data['department'] ?? $technician->department,
                'position'    => $data['position'] ?? $technician->position,
                'phone'       => $data['phone'] ?? $technician->phone,
                'id_document' => $data['id_document'] ?? $technician->id_document,
            ]);
        });

        // respuesta
        $technician->load(['user:id,first_name,last_name,email,phone', 'municipality:id,name,slug']);
        return response()->json([
            'user'    => $technician->user,
            'profile' => $technician,
        ]);
    }

    // DELETE /municipalities/{municipality}/technicians/{technician}
    public function destroy(Request $request, Municipality $municipality, TechnicianProfile $technician) {
        if ((int)$technician->municipality_id !== (int)$municipality->id) {
            abort(404);
        }

        DB::transaction(function () use ($technician) {
            // quitar rol de técnico al usuario (conservamos el usuario)
            if ($technician->user->hasRole(RoleName::TECHNICIAN->value)) {
                $technician->user->removeRole(RoleName::TECHNICIAN->value);
            }
            $technician->delete();
        });

        return response()->json(['message' => 'Técnico eliminado'], 200);
    }
}
