<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndAdminSeeder extends Seeder
{
    public function run(): void
    {
        // ----- Permisos base (ajusta/añade cuando avance el dominio) -----
        $permissions = [
            // administración global
            'admin.access',

            // gestión municipal
            'municipality.manage',     // alta/baja/edición municipio
            'zone.manage',             // zonas de parking y restricciones
            'police.manage',           // alta/baja/edición policías

            // operaciones
            'occupancy.view',          // ver ocupación/estadísticas
            'parking.register',        // que un usuario registre aparcamiento
            'vehicle.manage',          // CRUD vehículos propios
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(
                ['name' => $perm, 'guard_name' => 'api']
            );
        }

        // ----- Roles mínimos -----
        $roles = [
            'admin' => [
                'admin.access',
                'municipality.manage',
                'zone.manage',
                'police.manage',
                'occupancy.view',
                'parking.register',
                'vehicle.manage',
            ],
            'municipal_admin' => [
                'municipality.manage',
                'zone.manage',
                'police.manage',
                'occupancy.view',
            ],
            'police' => [
                'occupancy.view',
            ],
            'user' => [
                'parking.register',
                'vehicle.manage',
            ],
        ];

        foreach ($roles as $roleName => $perms) {
            $role = Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'api']
            );
            $role->syncPermissions($perms);
        }

        // ----- Usuario admin principal -----
        $admin = User::updateOrCreate(
            ['email' => 'admin@aparca.local'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('admin1234'), // cambia en prod
            ]
        );

        // asignar rol admin
        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }
    }
}
