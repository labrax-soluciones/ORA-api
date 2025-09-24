<?php

namespace App\Http\Controllers;

use App\Enums\RoleName;
use App\Http\Requests\PoliceStoreRequest;
use App\Http\Requests\PoliceUpdateRequest;
use App\Models\Municipality;
use App\Models\PoliceProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PoliceController extends Controller {
    // GET /municipalities/{municipality}/police
    public function index(Request $request, Municipality $municipality) {
        $items = PoliceProfile::query()
            ->with(['user:id,first_name,last_name,email,phone'])
            ->where('municipality_id', $municipality->id)
            ->orderBy('id', 'desc')
            ->paginate($request->integer('per_page', 20));

        return response()->json($items);
    }

    // POST /municipalities/{municipality}/police
    public function store(PoliceStoreRequest $request, Municipality $municipality) {
        $data = $request->validated();

        $user = DB::transaction(function () use ($data, $municipality) {
            $user = User::create([
                'email'       => $data['email'],
                'first_name'  => $data['first_name'],
                'last_name'   => $data['last_name'],
                'name'        => trim($data['first_name'] . ' ' . $data['last_name']),
                'phone'       => $data['phone'] ?? null,
                'id_document' => $data['id_document'] ?? null,
                // password temporal; en real envía invitación
                'password'    => Hash::make(str()->password(12)),
            ]);

            $user->assignRole(RoleName::POLICE->value);

            PoliceProfile::create([
                'user_id'         => $user->id,
                'municipality_id' => $municipality->id,
                'badge_number'    => $data['badge_number'],
                'rank'            => $data['rank'] ?? null,
                'phone'           => $data['phone'] ?? null,
                'id_document'     => $data['id_document'] ?? null,
            ]);

            return $user;
        });

        $profile = $user->policeProfile()->with('municipality')->first();

        return response()->json([
            'user'    => $user->only(['id', 'first_name', 'last_name', 'email', 'phone']),
            'profile' => $profile,
        ], 201);
    }

    // PUT /municipalities/{municipality}/police/{police}
    public function update(PoliceUpdateRequest $request, Municipality $municipality, PoliceProfile $police) {
        if ((int)$police->municipality_id !== (int)$municipality->id) {
            abort(404);
        }

        $data = $request->validated();

        DB::transaction(function () use ($police, $data) {
            // user
            $police->user->update([
                'email'       => $data['email'],
                'first_name'  => $data['first_name'],
                'last_name'   => $data['last_name'],
                'phone'       => $data['phone'] ?? $police->user->phone,
                'id_document' => $data['id_document'] ?? $police->user->id_document,
            ]);

            // profile
            $police->update([
                'badge_number' => $data['badge_number'],
                'rank'         => $data['rank'] ?? $police->rank,
                'phone'        => $data['phone'] ?? $police->phone,
                'id_document'  => $data['id_document'] ?? $police->id_document,
            ]);
        });

        $police->load(['user:id,first_name,last_name,email,phone', 'municipality:id,name,slug']);
        return response()->json([
            'user'    => $police->user,
            'profile' => $police,
        ]);
    }

    // DELETE /municipalities/{municipality}/police/{police}
    public function destroy(Request $request, Municipality $municipality, PoliceProfile $police) {
        if ((int)$police->municipality_id !== (int)$municipality->id) {
            abort(404);
        }

        DB::transaction(function () use ($police) {
            if ($police->user->hasRole(RoleName::POLICE->value)) {
                $police->user->removeRole(RoleName::POLICE->value);
            }
            $police->delete();
        });

        return response()->json(['message' => 'Policía eliminado'], 200);
    }
}
