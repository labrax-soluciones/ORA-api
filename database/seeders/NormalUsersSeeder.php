<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Enums\VehicleStatus;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class NormalUsersSeeder extends Seeder {
    public function run(): void {
        $rows = [
            [
                'email'       => 'ana.garcia@example.com',
                'first_name'  => 'Ana',
                'last_name'   => 'García',
                'phone'       => '600111222',
                'id_document' => 'DNI-12345678A',
                'password'    => 'password',

                // Perfil
                'profile' => [
                    'address_line1' => 'C/ Alcalá 123',
                    'address_line2' => null,
                    'city'          => 'Madrid',
                    'province'      => 'Madrid',
                    'postal_code'   => '28009',
                    'country'       => 'ES',
                    'date_of_birth' => '1990-05-17',
                    'secondary_phone' => null,
                    'meta'          => ['newsletter' => true],
                ],

                // Vehículos (nota: la misma matrícula se puede repetir en otro usuario)
                'vehicles' => [
                    [
                        'brand'         => 'SEAT',
                        'model'         => 'Ibiza',
                        'color'         => 'Rojo',
                        'license_plate' => '1234-ABC',
                        'status'        => VehicleStatus::Active->value,
                        'year'          => 2018,
                        'meta'          => ['fuel' => 'gasolina'],
                    ],
                    [
                        'brand'         => 'Tesla',
                        'model'         => 'Model 3',
                        'color'         => 'Negro',
                        'license_plate' => '5678-XYZ',
                        'status'        => VehicleStatus::Inactive->value,
                        'year'          => 2021,
                        'meta'          => ['battery_kwh' => 55],
                    ],
                ],
            ],

            [
                'email'       => 'carlos.lopez@example.com',
                'first_name'  => 'Carlos',
                'last_name'   => 'López',
                'phone'       => '600333444',
                'id_document' => 'DNI-87654321B',
                'password'    => 'password',

                'profile' => [
                    'address_line1'  => 'Av. del Puerto 45',
                    'address_line2'  => 'Piso 3ºB',
                    'city'           => 'Valencia',
                    'province'       => 'Valencia',
                    'postal_code'    => '46022',
                    'country'        => 'ES',
                    'date_of_birth'  => '1985-11-02',
                    'secondary_phone' => '960000000',
                    'meta'           => ['preferred_contact' => 'phone'],
                ],

                'vehicles' => [
                    [
                        'brand'         => 'Volkswagen',
                        'model'         => 'Golf',
                        'color'         => 'Blanco',
                        'license_plate' => '1234-ABC', // misma que Ana (válido entre usuarios)
                        'status'        => VehicleStatus::Active->value,
                        'year'          => 2016,
                        'meta'          => ['euro' => 6],
                    ],
                    [
                        'brand'         => 'Yamaha',
                        'model'         => 'XMAX 300',
                        'color'         => 'Azul',
                        'license_plate' => 'M-9999-J',
                        'status'        => VehicleStatus::Blocked->value,
                        'year'          => 2019,
                        'meta'          => ['type' => 'motorbike'],
                    ],
                ],
            ],

            [
                'email'       => 'maria.perez@example.com',
                'first_name'  => 'María',
                'last_name'   => 'Pérez',
                'phone'       => '600555666',
                'id_document' => 'DNI-11112222C',
                'password'    => 'password',

                'profile' => [
                    'address_line1' => 'Rua Nova 12',
                    'address_line2' => null,
                    'city'          => 'A Coruña',
                    'province'      => 'A Coruña',
                    'postal_code'   => '15001',
                    'country'       => 'ES',
                    'date_of_birth' => '1994-03-09',
                    'secondary_phone' => null,
                    'meta'          => ['marketing_opt_in' => false],
                ],

                'vehicles' => [
                    [
                        'brand'         => 'Peugeot',
                        'model'         => '208',
                        'color'         => 'Gris',
                        'license_plate' => 'AC-2025-Z',
                        'status'        => VehicleStatus::Active->value,
                        'year'          => 2020,
                        'meta'          => ['doors' => 5],
                    ],
                ],
            ],
        ];

        foreach ($rows as $data) {
            // Usuario
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'first_name'  => $data['first_name'],
                    'last_name'   => $data['last_name'],
                    'phone'       => $data['phone'] ?? null,
                    'id_document' => $data['id_document'] ?? null,
                    'password'    => Hash::make($data['password']),
                ]
            );

            // Rol USER
            if (!$user->hasRole(RoleName::USER->value)) {
                $user->assignRole(RoleName::USER->value);
            }

            // Perfil 1:1
            UserProfile::updateOrCreate(
                ['user_id' => $user->id],
                $data['profile']
            );

            // Vehículos (evita duplicar por la unique (user_id, license_plate))
            foreach ($data['vehicles'] as $v) {
                Vehicle::updateOrCreate(
                    [
                        'user_id'       => $user->id,
                        'license_plate' => $v['license_plate'],
                    ],
                    [
                        'brand'   => $v['brand'] ?? null,
                        'model'   => $v['model'] ?? null,
                        'color'   => $v['color'] ?? null,
                        'status'  => $v['status'] ?? VehicleStatus::Active->value,
                        'year'    => $v['year'] ?? null,
                        'meta'    => $v['meta'] ?? null,
                    ]
                );
            }
        }
    }
}
