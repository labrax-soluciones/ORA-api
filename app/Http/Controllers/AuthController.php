<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $remember = filter_var($request->input('remember', false), FILTER_VALIDATE_BOOL);

        // Política de tiempos:
        $accessTtlMinutes  = $remember ? 129600 : 1440; // 90 días o 1 día
        $refreshTtlMinutes = 129600;                    // ventana de refresh 90 días

        // Ajuste "al vuelo"
        JWTAuth::factory()->setTTL($accessTtlMinutes);
        config(['jwt.refresh_ttl' => $refreshTtlMinutes]);

        if (!$token = JWTAuth::claims(['remember' => $remember])->attempt($credentials)) {
            return response()->json(['error' => 'Credenciales inválidas'], 401);
        }

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => $accessTtlMinutes * 60,
            'remember'     => $remember,
        ]);
    }

    public function refresh(Request $request)
    {
        try {
            $remember = filter_var($request->input('remember', false), FILTER_VALIDATE_BOOL);

            $accessTtlMinutes  = $remember ? 129600 : 1440;
            $refreshTtlMinutes = 129600;

            JWTAuth::factory()->setTTL($accessTtlMinutes);
            config(['jwt.refresh_ttl' => $refreshTtlMinutes]);

            $newToken = JWTAuth::parseToken()->refresh();

            return response()->json([
                'access_token' => $newToken,
                'token_type'   => 'bearer',
                'expires_in'   => $accessTtlMinutes * 60,
                'remember'     => $remember,
            ]);
        } catch (TokenExpiredException $e) {
            return response()->json(['message' => 'Token demasiado antiguo. Vuelve a iniciar sesión.'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['message' => 'Token inválido. Vuelve a iniciar sesión.'], 401);
        } catch (JWTException $e) {
            return response()->json(['message' => 'No se pudo refrescar el token.'], 401);
        }
    }

    public function me()
    {
        return response()->json(Auth::user());
    }

    public function logout()
    {
        try {
            $token = JWTAuth::getToken();
            if ($token) {
                JWTAuth::invalidate($token);
            }
        } catch (\Throwable $e) {
            // swallow
        }
        return response()->json(['message' => 'Sesión cerrada']);
    }
}
