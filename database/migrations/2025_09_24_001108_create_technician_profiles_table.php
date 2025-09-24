<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('technician_profiles', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('municipality_id')->constrained()->cascadeOnDelete();

            // Datos propios del técnico (añade/quita según necesites)
            $table->string('department')->nullable();
            $table->string('position')->nullable();
            $table->string('phone')->nullable();        // si no quieres poner teléfono en users
            $table->string('id_document')->nullable();  // DNI/NIE si lo quieres aquí y no en users
            $table->string('avatar_path')->nullable();  // o integra más tarde Media Library
            $table->json('meta')->nullable();           // por si quieres flexibilidad

            $table->timestamps();

            // Reglas:
            $table->unique('user_id'); // un usuario solo puede tener UN perfil de técnico
            $table->index(['municipality_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('technician_profiles');
    }
};
