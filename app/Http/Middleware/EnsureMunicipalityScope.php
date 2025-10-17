<?php

namespace App\Http\Middleware;

use App\Models\Municipality;
use Closure;
use Illuminate\Http\Request;

class EnsureMunicipalityScope {
    public function handle(Request $request, Closure $next) {
        $user = $request->user();

        // 1) Resolver municipio desde la ruta (gracias al binding mixto)
        $routeParam = $request->route('municipality');
        $municipality = null;
        if ($routeParam instanceof Municipality) {
            $municipality = $routeParam;
        } elseif (is_numeric($routeParam)) {
            $municipality = Municipality::findOrFail((int) $routeParam);
        }

        // 2) Slug por header (opcional)
        $slugHeader = $request->header('X-Municipality-Slug');

        // Si NO viene en la ruta pero SÍ en header, puedes permitirlo:
        if (!$municipality && $slugHeader) {
            $municipality = Municipality::where('slug', $slugHeader)->firstOrFail();
            // (opcional) Inyectar para downstream:
            // $request->attributes->set('municipality', $municipality);
        }

        // Si vienen ambos, valida coherencia
        if ($municipality && $slugHeader) {
            if (strcasecmp($municipality->slug, $slugHeader) !== 0) {
                return response()->json(['message' => 'Municipio de ruta y header no coinciden.'], 400);
            }
        }

        // Si a estas alturas no hay municipio, no hay scope que validar
        if (!$municipality) {
            return $next($request);
        }

        $municipalityId = (int) $municipality->getKey();

        // --- Autorización por rol/perfil (tu lógica original) ---
        if ($user->hasRole('admin')) {
            return $next($request);
        }

        if ($user->hasRole('municipal_admin')) {
            $profile = $user->municipalAdminProfile;
            if ($profile && (int) $profile->municipality_id === $municipalityId) {
                return $next($request);
            }
            return response()->json(['message' => 'Fuera de ámbito (municipio)1'], 403);
        }

        if ($user->hasRole('technician')) {
            $profile = $user->technicianProfile;
            if ($profile && (int) $profile->municipality_id === $municipalityId) {
                return $next($request);
            }
            return response()->json(['message' => 'Fuera de ámbito (municipio)2.'], 403);
        }

        if ($user->hasRole('police')) {
            $profile = $user->policeProfile;
            if ($profile && (int) $profile->municipality_id === $municipalityId) {
                return $next($request);
            }
            return response()->json(['message' => 'Fuera de ámbito (municipio)3.'], 403);
        }

        return response()->json(['message' => 'No autorizado para este municipio.'], 403);
    }
}
