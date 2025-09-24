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

class MunicipalitySeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'name'           => 'Madrid',
                'slug'           => 'madrid',
                'timezone'       => 'Europe/Madrid',
                'default_locale' => 'es',
                'locales'        => ['es', 'en'],
                'sso_domains'    => ['madrid.es'],
                'contact_email'  => 'contacto@madrid.es',
                'contact_phone'  => '+34 910000000',
                'status'         => 'active',
                'settings'       => ['max_parking_hours' => 4],
            ],
            [
                'name'           => 'Valencia',
                'slug'           => 'valencia',
                'timezone'       => 'Europe/Madrid',
                'default_locale' => 'es',
                'locales'        => ['es', 'en', 'fr'],
                'sso_domains'    => ['valencia.es'],
                'contact_email'  => 'info@valencia.es',
                'contact_phone'  => '+34 960000000',
                'status'         => 'active',
                'settings'       => ['max_parking_hours' => 3],
            ],
            [
                'name'           => 'A Coruna',
                'slug'           => 'a-coruna',
                'timezone'       => 'Europe/Madrid',
                'default_locale' => 'gl',
                'locales'        => ['gl', 'es', 'en'],
                'sso_domains'    => ['coruna.gal'],
                'contact_email'  => 'atencion@coruna.gal',
                'contact_phone'  => '+34 981000000',
                'status'         => 'active',
                'settings'       => ['max_parking_hours' => 5],
            ],
        ];

        foreach ($rows as $data) {
            $mun = Municipality::firstOrCreate(['slug' => $data['slug']], $data);

            // Solo poblamos Madrid con personal de prueba
            if ($mun->slug === 'madrid') {
                $this->seedStaff($mun);
            }
        }
    }

    private function seedStaff(Municipality $municipality): void
    {
        // 1) Municipal Admin (+ perfil)
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
}
