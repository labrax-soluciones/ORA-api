<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Municipality;

class MunicipalitySeeder extends Seeder {
    public function run(): void {
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
            Municipality::firstOrCreate(['slug' => $data['slug']], $data);
        }
    }
}
