<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('municipalities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);                  // nombre visible
            $table->string('slug', 150)->unique();        // identificador único (multi-tenant)
            $table->string('timezone', 64)->default('Europe/Madrid');
            $table->string('default_locale', 5)->default('es');
            $table->json('locales');                      // idiomas habilitados (array)
            $table->json('sso_domains')->nullable();      // dominios SSO permitidos (array)
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 30)->nullable();
            $table->string('status', 16)->default('active'); // casteado a enum
            $table->json('settings')->nullable();         // config avanzada por municipio
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['name']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('municipalities');
    }
};
