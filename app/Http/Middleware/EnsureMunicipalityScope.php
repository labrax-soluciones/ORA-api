<?php

namespace App\Http\Middleware;

use App\Models\Municipality;
use Closure;
use Illuminate\Http\Request;

class EnsureMunicipalityScope {
    public function handle(Request $request, Closure $next) {
        $user = $request->user();

        // Resolver ID de municipio desde el parámetro de ruta
        $routeParam = $request->route('municipality');
        if ($routeParam instanceof Municipality) {
            $municipalityId = $routeParam->getKey();
        } elseif (is_numeric($routeParam)) {
            $municipalityId = (int) $routeParam;
        } else {
            // Si la ruta no lleva municipio, no hay scope que validar
            return $next($request);
        }

        // Admin global: bypass
        if ($user->hasRole('admin')) {
            return $next($request);
        }

        // Municipal admin: debe coincidir su municipio
        if ($user->hasRole('municipal_admin')) {
            $profile = $user->municipalAdminProfile;
            if ($profile && (int) $profile->municipality_id === (int) $municipalityId) {
                return $next($request);
            }
            return response()->json(['message' => 'Fuera de ámbito (municipio).'], 403);
        }

        // Technician: debe coincidir su municipio
        if ($user->hasRole('technician')) {
            $profile = $user->technicianProfile;
            if ($profile && (int) $profile->municipality_id === (int) $municipalityId) {
                return $next($request);
            }
            return response()->json(['message' => 'Fuera de ámbito (municipio).'], 403);
        }

        // Police: debe coincidir su municipio (aunque luego permisos limiten edición/lectura)
        if ($user->hasRole('police')) {
            $profile = $user->policeProfile;
            if ($profile && (int) $profile->municipality_id === (int) $municipalityId) {
                return $next($request);
            }
            return response()->json(['message' => 'Fuera de ámbito (municipio).'], 403);
        }

        // Otros roles: bloquear por defecto
        return response()->json(['message' => 'No autorizado para este municipio.'], 403);
    }
}
