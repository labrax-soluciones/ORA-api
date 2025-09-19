<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class AuthController extends Controller {
    public function login(Request $request) {
        $credentials = $request->only('email', 'password');
        $remember = filter_var($request->input('remember', false), FILTER_VALIDATE_BOOL);

        // Política de tiempos:
        // - remember = true  → access TTL muy largo (90 días)
        // - remember = false → access TTL moderado (1 día) pero con refresh hasta 90 días
        $accessTtlMinutes  = $remember ? 129600 : 1440;   // 90 días o 1 día
        $refreshTtlMinutes = 129600;                      // ventana de refresh: 90 días

        // Ajusta TTLs “al vuelo” solo para esta petición
        JWTAuth::factory()->setTTL($accessTtlMinutes);
        config(['jwt.refresh_ttl' => $refreshTtlMinutes]);

        // Emitimos token con claim 'remember'
        if (!$token = JWTAuth::claims(['remember' => $remember])->attempt($credentials)) {
            return response()->json(['error' => 'Credenciales inválidas'], 401);
        }

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => $accessTtlMinutes * 60,
            'remember'     => $remember, // opcional, útil si quieres leerlo en el front
        ]);
    }

    public function refresh(Request $request) {
        try {
            // El front indica si este dispositivo es "recordado"
            $remember = filter_var($request->input('remember', false), FILTER_VALIDATE_BOOL);

            // Política (ajusta a tu gusto):
            $accessTtlMinutes  = $remember ? 129600 : 1440; // 90 días o 1 día
            $refreshTtlMinutes = 129600;                    // ventana de refresh 90 días

            JWTAuth::factory()->setTTL($accessTtlMinutes);
            config(['jwt.refresh_ttl' => $refreshTtlMinutes]);

            // IMPORTANTE: refrescar directamente, sin leer payload antes
            $newToken = JWTAuth::parseToken()->refresh();

            return response()->json([
                'access_token' => $newToken,
                'token_type'   => 'bearer',
                'expires_in'   => $accessTtlMinutes * 60,
                'remember'     => $remember,
            ]);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            // Aquí sí: fuera de refresh_ttl o token imposible de refrescar
            return response()->json(['message' => 'Token demasiado antiguo. Vuelve a iniciar sesión.'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['message' => 'Token inválido. Vuelve a iniciar sesión.'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['message' => 'No se pudo refrescar el token.'], 401);
        }
    }


    public function me() {
        return response()->json(Auth::user());
    }

    public function logout() {
        try {
            $token = JWTAuth::getToken();
            if ($token) {
                JWTAuth::invalidate($token);
            }
        } catch (\Throwable $e) { /* ignore */
        }

        return response()->json(['message' => 'Sesión cerrada']);
    }
}
