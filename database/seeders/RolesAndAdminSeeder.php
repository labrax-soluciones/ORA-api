<?php

namespace Database\Seeders;

use App\Enums\PermissionName;
use App\Enums\RoleName;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndAdminSeeder extends Seeder {
    public function run(): void {
        // Limpia caché de permisos por si ejecutas varias veces
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // 1) Crear TODOS los permisos a partir del Enum
        foreach (PermissionName::cases() as $permCase) {
            Permission::firstOrCreate(
                ['name' => $permCase->value, 'guard_name' => 'api']
            );
        }

        // 2) Definir los permisos por rol (usando enums)
        $roles = [
            RoleName::ADMIN->value => [
                PermissionName::ADMIN_ACCESS->value,
                PermissionName::MUNICIPALITY_MANAGE->value,
                PermissionName::ZONE_MANAGE->value,
                PermissionName::POLICE_MANAGE->value,
                PermissionName::OCCUPANCY_VIEW->value,
                PermissionName::PARKING_REGISTER->value,
                PermissionName::VEHICLE_MANAGE->value,
            ],
            RoleName::MUNICIPAL_ADMIN->value => [
                PermissionName::MUNICIPALITY_MANAGE->value,
                PermissionName::ZONE_MANAGE->value,
                PermissionName::POLICE_MANAGE->value,
                PermissionName::OCCUPANCY_VIEW->value,
            ],
            RoleName::TECHNICIAN->value => [
                // Ajusta si el técnico no debe gestionar policías:
                PermissionName::MUNICIPALITY_MANAGE->value,
                PermissionName::ZONE_MANAGE->value,
                PermissionName::POLICE_MANAGE->value,
                PermissionName::OCCUPANCY_VIEW->value,
            ],
            RoleName::POLICE->value => [
                PermissionName::OCCUPANCY_VIEW->value,
            ],
            RoleName::USER->value => [
                PermissionName::PARKING_REGISTER->value,
                PermissionName::VEHICLE_MANAGE->value,
            ],
        ];

        // 3) Crear roles y sincronizar permisos
        foreach ($roles as $roleName => $permList) {
            $role = Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'api']
            );
            $role->syncPermissions($permList);
        }

        // 4) Usuario admin principal
        $admin = User::updateOrCreate(
            ['email' => 'admin@aparca.local'],
            [
                // con tu booted() esto se actualizará desde first/last si los añades
                'name'       => 'Super Admin',
                'first_name' => 'Super',
                'last_name'  => 'Admin',
                'password'   => Hash::make('admin1234'), // cambia en prod
            ]
        );

        if (!$admin->hasRole(RoleName::ADMIN->value)) {
            $admin->assignRole(RoleName::ADMIN->value);
        }

        // refresca caché
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
