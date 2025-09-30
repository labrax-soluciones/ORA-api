<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // 1) Eliminar columnas que ya no usamos (si existen)
        Schema::table('municipalities', function (Blueprint $table) {
            foreach (['timezone', 'default_locale', 'locales', 'sso_domains'] as $col) {
                if (Schema::hasColumn('municipalities', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        // 2) Normalizar columnas que se quedan (ahora sí podemos usar ->change() gracias a DBAL)
        Schema::table('municipalities', function (Blueprint $table) {
            // name / slug NOT NULL
            if (Schema::hasColumn('municipalities', 'name')) {
                $table->string('name')->nullable(false)->change();
            } else {
                $table->string('name')->nullable(false);
            }

            if (Schema::hasColumn('municipalities', 'slug')) {
                $table->string('slug')->nullable(false)->change();
            } else {
                $table->string('slug')->nullable(false);
            }

            // contact_email / contact_phone NULLABLE
            if (Schema::hasColumn('municipalities', 'contact_email')) {
                $table->string('contact_email')->nullable()->change();
            } else {
                $table->string('contact_email')->nullable();
            }

            if (Schema::hasColumn('municipalities', 'contact_phone')) {
                $table->string('contact_phone', 32)->nullable()->change();
            } else {
                $table->string('contact_phone', 32)->nullable();
            }

            // status NOT NULL DEFAULT 'active'
            if (Schema::hasColumn('municipalities', 'status')) {
                $table->string('status', 32)->default('active')->nullable(false)->change();
            } else {
                $table->string('status', 32)->default('active');
            }

            // settings JSON NULL
            if (Schema::hasColumn('municipalities', 'settings')) {
                $table->json('settings')->nullable()->change();
            } else {
                $table->json('settings')->nullable();
            }
        });

        // 3) Asegurar índice único en slug (solo si no existe)
        $exists = DB::selectOne("
            SELECT COUNT(1) AS c
            FROM information_schema.statistics
            WHERE table_schema = DATABASE()
              AND table_name = 'municipalities'
              AND index_name = 'municipalities_slug_unique'
        ");
        if ((int)($exists->c ?? 0) === 0) {
            Schema::table('municipalities', function (Blueprint $table) {
                $table->unique('slug', 'municipalities_slug_unique');
            });
        }
    }

    public function down(): void {
        // Quitar índice único si existe
        try {
            Schema::table('municipalities', function (Blueprint $table) {
                $table->dropUnique('municipalities_slug_unique');
            });
        } catch (\Throwable $e) {
            // ignorar si no existe
        }

        // Restaurar columnas eliminadas como NULL
        Schema::table('municipalities', function (Blueprint $table) {
            foreach (['timezone', 'default_locale'] as $col) {
                if (!Schema::hasColumn('municipalities', $col)) {
                    $table->string($col)->nullable();
                }
            }
            if (!Schema::hasColumn('municipalities', 'locales')) {
                $table->json('locales')->nullable();
            }
            if (!Schema::hasColumn('municipalities', 'sso_domains')) {
                $table->json('sso_domains')->nullable();
            }
        });

        // Devolver columnas a NULLABLE para rollback seguro
        Schema::table('municipalities', function (Blueprint $table) {
            if (Schema::hasColumn('municipalities', 'name')) {
                $table->string('name')->nullable()->change();
            }
            if (Schema::hasColumn('municipalities', 'slug')) {
                $table->string('slug')->nullable()->change();
            }
            if (Schema::hasColumn('municipalities', 'contact_email')) {
                $table->string('contact_email')->nullable()->change();
            }
            if (Schema::hasColumn('municipalities', 'contact_phone')) {
                $table->string('contact_phone', 32)->nullable()->change();
            }
            if (Schema::hasColumn('municipalities', 'status')) {
                $table->string('status', 32)->nullable()->change();
            }
            if (Schema::hasColumn('municipalities', 'settings')) {
                $table->json('settings')->nullable()->change();
            }
        });
    }
};
