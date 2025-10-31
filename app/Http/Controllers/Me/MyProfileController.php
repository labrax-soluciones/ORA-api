<?php

namespace App\Http\Controllers\Me;

use App\Http\Controllers\Controller;
use App\Http\Requests\Me\UpdateMyProfileRequest;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MyProfileController extends Controller
{
    // GET /me/profile
    public function show(Request $request)
    {
        $user = $request->user()->loadMissing('profile');
        return response()->json([
            'user'    => $user->only(['id','first_name','last_name','email','phone','id_document','avatar_path']),
            'profile' => $user->profile,
        ]);
    }

    // PUT/PATCH /me/profile
    public function update(UpdateMyProfileRequest $request)
    {
        $user   = $request->user();
        $data   = $request->validated();
        $uData  = $data['user'] ?? [];
        $pData  = $data['profile'] ?? [];

        DB::transaction(function () use ($user, $uData, $pData) {
            if (!empty($uData)) {
                // Evita colisión de email si viene
                if (isset($uData['email'])) {
                    $user->email = $uData['email'];
                }
                $user->first_name  = $uData['first_name'] ?? $user->first_name;
                $user->last_name   = $uData['last_name']  ?? $user->last_name;
                $user->phone       = $uData['phone']      ?? $user->phone;
                $user->id_document = $uData['id_document']?? $user->id_document;
                $user->save();
            }

            if (!empty($pData)) {
                $profile = $user->profile ?: new UserProfile(['user_id' => $user->id]);
                $profile->fill($pData);
                $profile->save();
            }
        });

        $user->load('profile');

        return response()->json([
            'user'    => $user->only(['id','first_name','last_name','email','phone','id_document','avatar_path']),
            'profile' => $user->profile,
        ]);
    }
}
