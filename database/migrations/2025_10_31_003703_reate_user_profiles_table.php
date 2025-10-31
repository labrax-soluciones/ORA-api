<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();

            // Relación 1:1 con users
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Dirección y datos adicionales (no ligados a un municipio)
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 2)->nullable(); // ISO-3166-1 alpha-2, ej. 'ES'

            $table->date('date_of_birth')->nullable();

            // Datos opcionales adicionales
            $table->string('secondary_phone')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->unique('user_id'); // 1 perfil por usuario
        });
    }

    public function down(): void {
        Schema::dropIfExists('user_profiles');
    }
};
