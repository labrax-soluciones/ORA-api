<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\Municipality;
use App\Models\User;
use App\Models\TechnicianProfile;
use App\Models\PoliceProfile;
use App\Models\MunicipalAdminProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MunicipalitySeeder extends Seeder {
    public function run(): void {
        $rows = [
            [
                'name'          => 'Madrid',
                'slug'          => 'madrid',
                'contact_email' => 'contacto@madrid.es',
                'contact_phone' => '+34 910000000',
                'status'        => 'active',
                'settings'      => ['max_parking_hours' => 4],
            ],
            [
                'name'          => 'Valencia',
                'slug'          => 'valencia',
                'contact_email' => 'info@valencia.es',
                'contact_phone' => '+34 960000000',
                'status'        => 'active',
                'settings'      => ['max_parking_hours' => 3],
            ],
            [
                'name'          => 'A Coruna',
                'slug'          => 'a-coruna',
                'contact_email' => 'atencion@coruna.gal',
                'contact_phone' => '+34 981000000',
                'status'        => 'active',
                'settings'      => ['max_parking_hours' => 5],
            ],
        ];

        foreach ($rows as $data) {
            // usamos slug como clave natural
            $mun = Municipality::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'name'          => $data['name'],
                    'contact_email' => $data['contact_email'],
                    'contact_phone' => $data['contact_phone'],
                    'status'        => $data['status'],
                    'settings'      => $data['settings'],
                ]
            );

            if ($mun->slug === 'madrid') {
                $this->seedStaff($mun);
            }

            $this->seedParkingZones($mun);
        }
    }

    private function seedStaff(Municipality $municipality): void {
        // 1) Municipal Admin
        $admin = User::updateOrCreate(
            ['email' => 'mad_admin@aparca.local'],
            [
                'first_name' => 'Madrid',
                'last_name'  => 'Admin',
                'name'       => 'Madrid Admin',
                'password'   => Hash::make('password'),
            ]
        );
        $admin->assignRole(RoleName::MUNICIPAL_ADMIN->value);

        MunicipalAdminProfile::updateOrCreate(
            ['user_id' => $admin->id],
            [
                'municipality_id' => $municipality->id,
                'phone'           => '690000000',
                'id_document'     => 'DOC-ADM-MAD',
            ]
        );

        // 2) Technicians (2)
        for ($i = 1; $i <= 2; $i++) {
            $tech = User::updateOrCreate(
                ['email' => "tech{$i}@madrid.aparca.local"],
                [
                    'first_name' => "Tech{$i}",
                    'last_name'  => 'Madrid',
                    'name'       => "Tech{$i} Madrid",
                    'password'   => Hash::make('password'),
                ]
            );
            $tech->assignRole(RoleName::TECHNICIAN->value);

            TechnicianProfile::updateOrCreate(
                ['user_id' => $tech->id],
                [
                    'municipality_id' => $municipality->id,
                    'department'      => 'Movilidad',
                    'position'        => "Técnico {$i}",
                    'phone'           => "60000000{$i}",
                    'id_document'     => "DOC-T{$i}",
                ]
            );
        }

        // 3) Police (5)
        for ($i = 1; $i <= 5; $i++) {
            $police = User::updateOrCreate(
                ['email' => "police{$i}@madrid.aparca.local"],
                [
                    'first_name' => "Policia{$i}",
                    'last_name'  => 'Madrid',
                    'name'       => "Policia{$i} Madrid",
                    'password'   => Hash::make('password'),
                ]
            );
            $police->assignRole(RoleName::POLICE->value);

            PoliceProfile::updateOrCreate(
                ['user_id' => $police->id],
                [
                    'municipality_id' => $municipality->id,
                    'badge_number'    => "MAD-P{$i}",
                    'rank'            => 'Agente',
                    'phone'           => "61000000{$i}",
                ]
            );
        }
    }

    private function seedParkingZones(Municipality $municipality): void {
        // 1) Tipos de zona de aparcamiento
        $blue = \App\Models\ParkingZoneType::updateOrCreate(
            ['municipality_id' => $municipality->id, 'slug' => 'blue-zone'],
            [
                'name' => 'Blue zone',
                'color_hex' => '#1976d2',
                'max_stay_minutes' => 90, // 1h 30m
                'outside_schedule_policy' => 'unlimited',
                'status' => 'active',
                'settings' => ['paid' => true],
            ]
        );

        $motorhome = \App\Models\ParkingZoneType::updateOrCreate(
            ['municipality_id' => $municipality->id, 'slug' => 'motorhome-zone'],
            [
                'name' => 'Motorhome zone',
                'color_hex' => '#2e7d32',
                'max_stay_minutes' => 3 * 24 * 60, // 3 días
                'outside_schedule_policy' => 'unlimited',
                'status' => 'active',
                'settings' => ['oversize_allowed' => true],
            ]
        );

        // 2) Horarios solo para el tipo "Blue zone":
        // L-V 08:00-17:00, Sáb 08:00-14:00, Domingo sin horario
        foreach ([1, 2, 3, 4, 5] as $dow) {
            \App\Models\ParkingZoneTypeSchedule::updateOrCreate(
                [
                    'parking_zone_type_id' => $blue->id,
                    'day_of_week' => $dow,
                    'start_time' => '08:00:00',
                    'end_time' => '17:00:00',
                ],
                [
                    'timezone' => $municipality->timezone ?? 'Europe/Madrid',
                    'is_holiday' => false,
                ]
            );
        }
        \App\Models\ParkingZoneTypeSchedule::updateOrCreate(
            [
                'parking_zone_type_id' => $blue->id,
                'day_of_week' => 6,
                'start_time' => '08:00:00',
                'end_time' => '14:00:00',
            ],
            [
                'timezone' => $municipality->timezone ?? 'Europe/Madrid',
                'is_holiday' => false,
            ]
        );
        // Domingo (7) sin tramos => aplica outside_schedule_policy

        // 3) Zonas de aparcamiento (geocercas) de ejemplo
        \App\Models\ParkingZone::updateOrCreate(
            ['municipality_id' => $municipality->id, 'slug' => 'plaza-mayor-blue'],
            [
                'parking_zone_type_id' => $blue->id,
                'name' => 'Plaza Mayor (Blue)',
                'description' => 'Área regulada zona azul alrededor de Plaza Mayor.',
                'capacity' => 120,
                'status' => 'active',
                'geometry' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [-3.7095, 40.4150],
                        [-3.7075, 40.4150],
                        [-3.7075, 40.4140],
                        [-3.7095, 40.4140],
                        [-3.7095, 40.4150],
                    ]],
                    'crs' => ['type' => 'name', 'properties' => ['name' => 'EPSG:4326']],
                ],
                'metadata' => ['notes' => 'Ejemplo demo'],
            ]
        );

        \App\Models\ParkingZone::updateOrCreate(
            ['municipality_id' => $municipality->id, 'slug' => 'rv-casadecampo'],
            [
                'parking_zone_type_id' => $motorhome->id,
                'name' => 'Área RV Casa de Campo',
                'description' => 'Zona para autocaravanas con límite de 3 días.',
                'capacity' => 30,
                'status' => 'active',
                'geometry' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [-3.7430, 40.4215],
                        [-3.7400, 40.4215],
                        [-3.7400, 40.4195],
                        [-3.7430, 40.4195],
                        [-3.7430, 40.4215],
                    ]],
                    'crs' => ['type' => 'name', 'properties' => ['name' => 'EPSG:4326']],
                ],
                'metadata' => ['surface' => 'asphalt'],
            ]
        );
    }
}
