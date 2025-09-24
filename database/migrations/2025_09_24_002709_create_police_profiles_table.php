<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('police_profiles', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('municipality_id')->constrained()->cascadeOnDelete();

            // Datos propios del policía
            $table->string('badge_number')->unique(); // nº de placa
            $table->string('rank')->nullable();       // rango (Agente, Inspector…)
            $table->string('phone')->nullable();
            $table->string('id_document')->nullable(); // DNI
            $table->string('avatar_path')->nullable();
            $table->json('meta')->nullable();          // extras opcionales

            $table->timestamps();

            $table->unique('user_id'); // un usuario solo puede tener un perfil de policía
            $table->index(['municipality_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('police_profiles');
    }
};
